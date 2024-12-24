<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Internal\Protocol\Binary\Response;
use Typhoon\Memcached\Stat;
use function Typhoon\Memcached\Internal\isNotEmptyString;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-extends Command<array<non-empty-string, Stat>>
 */
final class Stats extends Command
{
    /**
     * @param non-negative-int $id
     */
    public function __construct(
        private readonly int $id,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $header = new Header(
            Magic::REQUEST,
            Opcode::Stat,
            $this->id,
        );

        $header->write($writer);
    }

    protected function doParseResponse(Response $response): array
    {
        $stats = [];

        foreach ([$response, ...$response->responses] as $it) {
            if (isNotEmptyString($it->key) && isNotEmptyString($it->value)) {
                $stats[$it->key] = new Stat(
                    $it->key,
                    $it->value,
                );
            }
        }

        return $stats;
    }
}
