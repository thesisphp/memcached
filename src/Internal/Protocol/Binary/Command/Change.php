<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;
use Thesis\Memcached\Item;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-extends Command<void>
 */
final class Change extends Command
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

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = Header::asRequest(
            $this->opcode,
            $this->id,
            keyLength: \strlen($keyValue),
            totalBodyLength: \strlen($keyValue) + \strlen((string) $this->item->value),
        );

        $header->write($writer);

        $writer->write($keyValue);

        if (($value = (string) $this->item->value) !== '') {
            $writer->write($value);
        }
    }

    public function parseResponse(Response $response): void {}
}
