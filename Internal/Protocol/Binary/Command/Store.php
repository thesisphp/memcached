<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-extends Command<void>
 */
final class Store extends Command
{
    /**
     * @param non-negative-int $id
     */
    public static function set(int $id, Key $key, Item $item): self
    {
        return new self($id, Opcode::Set, $key, $item);
    }

    /**
     * @param non-negative-int $id
     */
    public static function add(int $id, Key $key, Item $item): self
    {
        return new self($id, Opcode::Add, $key, $item);
    }

    /**
     * @param non-negative-int $id
     */
    public static function replace(int $id, Key $key, Item $item): self
    {
        return new self($id, Opcode::Replace, $key, $item);
    }

    /**
     * @param non-negative-int $id
     */
    private function __construct(
        private readonly int $id,
        private readonly Opcode $opcode,
        private readonly Key $key,
        private readonly Item $item,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = new Header(
            Magic::REQUEST,
            $this->opcode,
            $this->id,
            keyLength: \strlen($keyValue),
            extrasLength: 8,
            totalBodyLength: 8 + \strlen($keyValue) + \strlen((string) $this->item->value),
            cas: $this->item->casId,
        );

        $header->write($writer);

        $writer
            ->writeUint32($this->item->flags)
            ->writeUint32($this->item->expiration?->value ?? 0);

        $writer->write($keyValue);
        $writer->write((string) $this->item->value);
    }
}
