<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Endian\endian;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;
use Thesis\Memcached\Item;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-extends Command<Item>
 */
final class Get extends Command
{
    /**
     * @param non-negative-int $id
     */
    public function __construct(
        private readonly int $id,
        private readonly Key $key,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = Header::asRequest(
            Opcode::Get,
            $this->id,
            keyLength: \strlen($keyValue),
            totalBodyLength: \strlen($keyValue),
        );

        $header->write($writer);

        $writer->write($keyValue);
    }

    public function parseResponse(Response $response): Item
    {
        return new Item(
            $response->value ?? '',
            flags: $response->extras !== null && $response->extras !== '' ? endian::network->unpackUint32($response->extras) : 0,
            casId: $response->header->cas,
        );
    }
}
