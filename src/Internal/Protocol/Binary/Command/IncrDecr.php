<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Endian\endian;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-extends Command<int>
 */
final class IncrDecr extends Command
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

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = Header::asRequest(
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

    public function parseResponse(Response $response): int
    {
        return $response->value !== null && $response->value !== '' ? endian::network->unpackUint64($response->value) : 0;
    }
}
