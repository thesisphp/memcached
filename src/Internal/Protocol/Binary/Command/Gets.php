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
use function Typhoon\Memcached\Internal\isNotEmptyString;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-extends Command<array<non-empty-string, Item>>
 */
final class Gets extends Command
{
    /**
     * @param non-negative-int $id
     * @param non-empty-list<Key> $keys
     */
    public function __construct(
        private readonly int $id,
        private readonly array $keys,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        foreach ($this->keys as $key) {
            $keyValue = (string) $key;

            $header = Header::asRequest(
                Opcode::GetKQ,
                $this->id,
                keyLength: \strlen($keyValue),
                totalBodyLength: \strlen($keyValue),
            );

            $header->write($writer);

            $writer->write($keyValue);
        }

        $header = Header::asRequest(
            Opcode::Noop,
            $this->id,
        );

        $header->write($writer);
    }

    protected function doParseResponse(Response $response): array
    {
        $items = [];

        foreach ([$response, ...$response->responses] as $it) {
            if (isNotEmptyString($it->key)) {
                $items[$it->key] = self::parseItem($it);
            }
        }

        return $items;
    }

    private static function parseItem(Response $response): Item
    {
        return new Item(
            $response->value ?? '',
            flags: $response->extras !== null && $response->extras !== '' ? endian::network->unpackUint32($response->extras) : 0,
            casId: $response->header->cas,
        );
    }
}
