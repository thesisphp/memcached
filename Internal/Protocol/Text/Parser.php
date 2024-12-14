<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

use Typhoon\Memcached\Exception\KeyAlreadyExists;
use Typhoon\Memcached\Exception\KeyNotFound;
use Typhoon\Memcached\Exception\KeyNotStored;
use Typhoon\Memcached\Exception\MemcachedClientError;
use Typhoon\Memcached\Exception\MemcachedServerError;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Stat;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @psalm-type ItemHeaderFormat = array{0: non-empty-string, 1: non-empty-string, 2: numeric-string, 3: numeric-string, 4?: numeric-string}
 * @psalm-type StatHeaderFormat = array{0: non-empty-string, 1: non-empty-string}
 */
final class Parser
{
    /** @var string */
    private const SEPARATOR = "\r\n";

    /** @var string */
    private const RESULT_STORED = 'STORED';

    /** @var string */
    private const RESULT_OK = 'OK';

    /** @var string */
    private const RESULT_TOUCHED = 'TOUCHED';

    /** @var string */
    private const RESULT_DELETED = 'DELETED';

    /** @var string */
    private const RESULT_NOT_STORED = 'NOT_STORED';

    /** @var string */
    private const RESULT_EXISTS = 'EXISTS';

    /** @var string */
    private const RESULT_NOT_FOUND = 'NOT_FOUND';

    /** @var string */
    private const RESULT_ERROR = 'ERROR';

    /** @var string */
    private const RESULT_END = 'END';

    /** @var string */
    private const CLIENT_ERROR_PREFIX = 'CLIENT_ERROR';

    /** @var string */
    private const SERVER_ERROR_PREFIX = 'SERVER_ERROR';

    /** @var string */
    private const VERSION_PREFIX = 'VERSION';

    /** @var string */
    private const VALUE_PREFIX = 'VALUE';

    /** @var string */
    private const STAT_PREFIX = 'STAT';

    /** @var list<non-empty-string> */
    private const COMPLETABLE = [
        self::RESULT_OK,
        self::RESULT_STORED,
        self::RESULT_TOUCHED,
        self::RESULT_DELETED,
    ];

    /** @var list<non-empty-string> */
    private const ERRONEOUS = [
        self::RESULT_EXISTS,
        self::RESULT_NOT_STORED,
        self::RESULT_NOT_FOUND,
        self::RESULT_ERROR,
    ];

    /** @var list<non-empty-string> */
    private array $buffer = [];

    /**
     * @param \Closure(Response): void $push
     */
    public function __construct(
        private readonly \Closure $push,
    ) {}

    /**
     * @param non-empty-string $line
     */
    public function push(string $line): void
    {
        [$line, $response] = [trim($line, self::SEPARATOR), null];

        if (\in_array($line, self::COMPLETABLE, true)) {
            $response = Response::ok();
        } elseif (str_starts_with($line, self::CLIENT_ERROR_PREFIX)) {
            $response = Response::error(new MemcachedClientError(substr($line, \strlen(self::CLIENT_ERROR_PREFIX) + 1)));
        } elseif (str_starts_with($line, self::SERVER_ERROR_PREFIX)) {
            $response = Response::error(new MemcachedServerError(substr($line, \strlen(self::SERVER_ERROR_PREFIX) + 1)));
        } elseif (str_starts_with($line, self::VERSION_PREFIX)) {
            $response = Response::ok(substr($line, \strlen(self::VERSION_PREFIX) + 1));
        } elseif (\in_array($line, self::ERRONEOUS, true)) {
            $response = match ($line) {
                self::RESULT_NOT_FOUND => Response::error(new KeyNotFound()),
                self::RESULT_EXISTS => Response::error(new KeyAlreadyExists()),
                self::RESULT_NOT_STORED => Response::error(new KeyNotStored()),
                self::RESULT_ERROR => Response::error(new MemcachedClientError('A nonexistent command was called.')),
            };
        } elseif (is_numeric($line)) {
            $response = Response::ok((int) $line);
        } elseif (str_ends_with($line, self::RESULT_END)) {
            [$buffer, $this->buffer] = [
                [...$this->buffer, substr($line, 0, \strlen($line) - \strlen(self::RESULT_END))],
                [],
            ];

            try {
                $response = self::parseResponse(implode(self::SEPARATOR, $buffer));
            } catch (\Throwable $e) {
                $response = Response::error($e);
            }
        } elseif ($line !== '') {
            $this->buffer[] = $line;
        }

        if ($response !== null) {
            ($this->push)($response);
        }
    }

    /**
     * @throws MemcachedServerError
     */
    private static function parseResponse(string $buffer): Response
    {
        return match (true) {
            str_starts_with($buffer, self::VALUE_PREFIX) => Response::ok([...self::parseItems($buffer)]),
            str_starts_with($buffer, self::STAT_PREFIX) => Response::ok([...self::parseStats($buffer)]),
            default => Response::ok(),
        };
    }

    /**
     * @return \Generator<non-empty-string, Item>
     * @throws MemcachedServerError
     */
    private static function parseItems(string $buffer): \Generator
    {
        $items = explode(self::SEPARATOR, trim($buffer, self::SEPARATOR));

        if (\count($items) % 2 !== 0) {
            throw new MemcachedServerError('Invalid item wire format.');
        }

        for ($i = 0; $i < \count($items); $i += 2) {
            /** @var ItemHeaderFormat $header */
            $header = explode(' ', $items[$i]);

            yield $header[1] => new Item(
                $items[$i + 1],
                flags: (int) $header[2],
                casId: (int) ($header[4] ?? 0),
            );
        }
    }

    /**
     * @return \Generator<non-empty-string, Stat>
     */
    private static function parseStats(string $buffer): \Generator
    {
        $stats = explode(self::SEPARATOR, trim($buffer, self::SEPARATOR));

        foreach ($stats as $stat) {
            $stat = substr($stat, \strlen(self::STAT_PREFIX) + 1);
            /** @var StatHeaderFormat $header */
            $header = explode(' ', $stat);

            yield $header[0] => new Stat(
                $header[0],
                $header[1],
            );
        }
    }
}
