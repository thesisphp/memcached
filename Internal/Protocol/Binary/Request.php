<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-covariant T
 */
final class Request implements Writable
{
    /**
     * @param non-negative-int $id
     */
    private function __construct(
        public readonly Opcode $opcode,
        public readonly int $id = 1,
        public readonly bool $withExtras = false,
        public readonly ?Key $key = null,
        public readonly ?Item $item = null,
    ) {}

    /**
     * @template E
     * @return self<E>
     */
    public static function fromOpcode(Opcode $opcode): self
    {
        return new self($opcode);
    }

    /**
     * @return self<string>
     */
    public static function version(): self
    {
        return self::fromOpcode(Opcode::Version);
    }

    /**
     * @param non-negative-int $id
     * @return self<T>
     */
    public function withId(int $id): self
    {
        return new self($this->opcode, $id, $this->withExtras, $this->key, $this->item);
    }

    /**
     * @return self<T>
     */
    public function withExtras(): self
    {
        return new self($this->opcode, $this->id, true, $this->key, $this->item);
    }

    /**
     * @return self<T>
     */
    public function withKey(Key $key): self
    {
        return new self($this->opcode, $this->id, $this->withExtras, $key, $this->item);
    }

    /**
     * @return self<T>
     */
    public function withItem(Item $item): self
    {
        return new self($this->opcode, $this->id, $this->withExtras, $this->key, $item);
    }

    /**
     * @return self<void>
     */
    public static function set(Key $key, Item $item): self
    {
        return self::store(Opcode::Set, $key, $item);
    }

    /**
     * @return self<void>
     */
    public static function add(Key $key, Item $item): self
    {
        return self::store(Opcode::Add, $key, $item);
    }

    public static function replace(Key $key, Item $item): self
    {
        return self::store(Opcode::Replace, $key, $item);
    }

    public static function append(Key $key, Item $item): self
    {
        return self::change(Opcode::Append, $key, $item);
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = $this->key !== null ? (string) $this->key : null;

        $header = new Header(
            Magic::REQUEST,
            $this->opcode,
            $this->id,
            keyLength: $keyValue !== null ? \strlen($keyValue) : 0,
            extrasLength: $this->withExtras ? 8 : 0,
            totalBodyLength: ($this->withExtras ? 8 : 0)
                + ($keyValue !== null ? \strlen($keyValue) : 0)
                + ($this->item !== null ? \strlen($this->item->value) : 0),
            cas: $this->item?->casId ?? 0,
        );

        $header->write($writer);

        if ($this->withExtras) {
            $writer
                ->writeUint32($this->item?->flags ?? 0)
                ->writeUint32($this->item?->expiration?->value ?? 0);
        }

        if ($keyValue !== null) {
            $writer->write($keyValue);
        }

        if ($this->item !== null) {
            $writer->write($this->item->value);
        }
    }

    /**
     * @return callable(Response): T
     */
    public function responseParser(): callable
    {
        return match ($this->opcode) {
            Opcode::Version => static fn(Response $response): string => $response->value ?? '',
            default => static fn() => throw new \Exception('Not implemented yet'),
        };
    }

    /**
     * @return self<void>
     */
    private static function change(Opcode $opcode, Key $key, Item $item): self
    {
        return self::fromOpcode($opcode)
            ->withKey($key)
            ->withItem($item);
    }

    /**
     * @return self<void>
     */
    private static function store(Opcode $opcode, Key $key, Item $item): self
    {
        return self::fromOpcode($opcode)
            ->withExtras()
            ->withKey($key)
            ->withItem($item);
    }
}
