<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Endian\endian;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Internal\Protocol\Binary\Response;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<int>
 */
final class IncrDecr implements Command
{
    /**
     * @param non-negative-int $id
     * @param non-negative-int $delta
     */
    public static function incr(int $id, Key $key, int $delta): self
    {
        return new self($id, Opcode::Increment, $key, $delta);
    }

    /**
     * @param non-negative-int $id
     * @param non-negative-int $delta
     */
    public static function decr(int $id, Key $key, int $delta): self
    {
        return new self($id, Opcode::Decrement, $key, $delta);
    }

    /**
     * @param non-negative-int $id
     * @param non-negative-int $delta
     */
    private function __construct(
        private readonly int $id,
        private readonly Opcode $opcode,
        private readonly Key $key,
        private readonly int $delta,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function parseResponse(Response $response): int
    {
        return $response->value !== null ? endian::network->unpackUint64($response->value) : 0;
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = new Header(
            Magic::REQUEST,
            $this->opcode,
            $this->id,
            keyLength: \strlen($keyValue),
            extrasLength: 20,
            totalBodyLength: 20 + \strlen($keyValue),
        );

        $header->write($writer);

        $writer
            ->writeUint64($this->delta)
            ->writeUint64(0)
            ->writeUint32(0);

        $writer->write($keyValue);
    }
}
