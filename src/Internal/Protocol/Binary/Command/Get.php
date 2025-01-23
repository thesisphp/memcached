<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Endian\endian;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Internal\Protocol\Binary\Response;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
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

    protected function doParseResponse(Response $response): Item
    {
        return new Item(
            $response->value ?? '',
            flags: $response->extras !== null && $response->extras !== '' ? endian::network->unpackUint32($response->extras) : 0,
            casId: $response->header->cas,
        );
    }
}
