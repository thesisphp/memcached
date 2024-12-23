<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-extends Command<void>
 */
final class Delete extends Command
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

        $header = new Header(
            Magic::REQUEST,
            Opcode::Delete,
            $this->id,
            keyLength: \strlen($keyValue),
            totalBodyLength: \strlen($keyValue),
        );

        $header->write($writer);

        $writer->write($keyValue);
    }
}
