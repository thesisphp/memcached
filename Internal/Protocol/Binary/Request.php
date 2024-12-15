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

    public static function prepend(Key $key, Item $item): self
    {
        return self::change(Opcode::Prepend, $key, $item);
    }

    public static function delete(Key $key): self
    {
        return self::change(Opcode::Delete, $key);
    }

    /**
     * @return self<int>
     */
    public static function increment(Key $key, Item $item): self
    {
        return self::incrDecr(Opcode::Increment, $key, $item);
    }

    /**
     * @return self<string>
     */
    public static function version(): self
    {
        return self::fromOpcode(Opcode::Version);
    }

    /**
     * @template E
     * @param ?callable(WriteTo): void $writeRequest
     * @return self<E>
     */
    private static function fromOpcode(Opcode $opcode, ?callable $writeRequest = null): self
    {
        return new self($opcode, $writeRequest);
    }

    /** @var callable(WriteTo, self): void */
    private $writeRequest;

    /**
     * @param ?callable(WriteTo, self): void $writeRequest
     * @param non-negative-int $id
     */
    private function __construct(
        public readonly Opcode $opcode,
        ?callable $writeRequest = null,
        public readonly int $id = 1,
        private readonly ?Key $key = null,
        private readonly ?Item $item = null,
    ) {
        $this->writeRequest = $writeRequest ?: static function (WriteTo $_): void {};
    }

    /**
     * @param non-negative-int $id
     * @return self<T>
     */
    public function withId(int $id): self
    {
        return new self($this->opcode, $this->writeRequest, $id, $this->key, $this->item);
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = $this->key !== null ? (string) $this->key : null;

        $header = match ($this->opcode) {
            Opcode::Version => new Header(
                Magic::REQUEST,
                $this->opcode,
                $this->id,
            ),
            Opcode::Set, Opcode::Add, Opcode::Replace => new Header(
                Magic::REQUEST,
                $this->opcode,
                $this->id,
                keyLength: $keyValue !== null ? \strlen($keyValue) : 0,
                extrasLength: 8,
                totalBodyLength: 8
                    + ($keyValue !== null ? \strlen($keyValue) : 0)
                    + \strlen((string) $this->item?->value ?? ''),
                cas: $this->item?->casId ?? 0,
            ),
            Opcode::Append, Opcode::Prepend => new Header(
                Magic::REQUEST,
                $this->opcode,
                $this->id,
                keyLength: $keyValue !== null ? \strlen($keyValue) : 0,
                totalBodyLength: ($keyValue !== null ? \strlen($keyValue) : 0) + \strlen((string) $this->item?->value ?? ''),
                cas: $this->item?->casId ?? 0,
            ),
            Opcode::Delete => new Header(
                Magic::REQUEST,
                $this->opcode,
                $this->id,
                keyLength: $keyValue !== null ? \strlen($keyValue) : 0,
                totalBodyLength: ($keyValue !== null ? \strlen($keyValue) : 0),
                cas: $this->item?->casId ?? 0,
            ),
            Opcode::Increment, Opcode::Decrement => new Header(
                Magic::REQUEST,
                $this->opcode,
                $this->id,
                keyLength: $keyValue !== null ? \strlen($keyValue) : 0,
                extrasLength: 20,
                totalBodyLength: 20 + ($keyValue !== null ? \strlen($keyValue) : 0),
                cas: $this->item?->casId ?? 0,
            ),
        };

        $header->write($writer);
        ($this->writeRequest)($writer, $this);
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
     * @return self<T>
     */
    private function withKey(Key $key): self
    {
        return new self($this->opcode, $this->writeRequest, $this->id, $key, $this->item);
    }

    /**
     * @return self<T>
     */
    private function withItem(Item $item): self
    {
        return new self($this->opcode, $this->writeRequest, $this->id, $this->key, $item);
    }

    private static function incrDecr(Opcode $opcode, Key $key, Item $item): self
    {
        return self::fromOpcode($opcode, static function (WriteTo $writer, self $request): void {
            $writer
                ->writeUint64((int) $request->item?->value ?? 0)
                ->writeUint64($request->item?->initialValue ?? 0)
                ->writeUint32($request->item?->expiration?->value ?? 0)
                ->write($request->key !== null ? (string) $request->key : '');
        })
            ->withKey($key)
            ->withItem($item);
    }

    /**
     * @return self<void>
     */
    private static function change(Opcode $opcode, Key $key, ?Item $item = null): self
    {
        $request = self::fromOpcode($opcode, static function (WriteTo $writer, self $request): void {
            $writer->write($request->key !== null ? (string) $request->key : '');
            $writer->write((string) $request->item?->value ?? '');
        })
            ->withKey($key);

        if ($item !== null) {
            $request = $request->withItem($item);
        }

        return $request;
    }

    /**
     * @return self<void>
     */
    private static function store(Opcode $opcode, Key $key, Item $item): self
    {
        return self::fromOpcode($opcode, static function (WriteTo $writer, self $request): void {
            $writer
                ->writeUint32($request->item?->flags ?? 0)
                ->writeUint32($request->item?->expiration?->value ?? 0);

            $writer->write($request->key !== null ? (string) $request->key : '');
            $writer->write((string) $request->item?->value ?? '');
        })
            ->withKey($key)
            ->withItem($item);
    }
}
