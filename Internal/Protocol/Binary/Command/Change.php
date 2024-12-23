<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Internal\Protocol\Binary\Response;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<void>
 */
final class Change implements Command
{
    /**
     * @param non-negative-int $id
     */
    public static function append(int $id, Key $key, Item $item): self
    {
        return new self($id, Opcode::Append, $key, $item);
    }

    /**
     * @param non-negative-int $id
     */
    public static function prepend(int $id, Key $key, Item $item): self
    {
        return new self($id, Opcode::Prepend, $key, $item);
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

    public function parseResponse(Response $response): void
    {
        $response->throwOnError();
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = new Header(
            Magic::REQUEST,
            $this->opcode,
            $this->id,
            keyLength: \strlen($keyValue),
            totalBodyLength: \strlen($keyValue) + \strlen((string) $this->item->value),
        );

        $header->write($writer);

        $writer->write($keyValue);
        $writer->write((string) $this->item->value);
    }
}
