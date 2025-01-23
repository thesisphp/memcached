<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Memcached\Expiration;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-extends Command<void>
 */
final class Touch extends Command
{
    /**
     * @param non-negative-int $id
     */
    public function __construct(
        private readonly int $id,
        private readonly Key $key,
        private readonly Expiration $expiration,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $keyValue = (string) $this->key;

        $header = Header::asRequest(
            Opcode::Touch,
            $this->id,
            keyLength: \strlen($keyValue),
            extrasLength: 4,
            totalBodyLength: 4 + \strlen($keyValue),
        );

        $header->write($writer);

        $writer
            ->writeUint32($this->expiration->value)
            ->write($keyValue);
    }

    public function parseResponse(Response $response): void {}
}
