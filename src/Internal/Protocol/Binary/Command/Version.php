<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;

/**
 * @internal
 * @template-extends Command<string>
 */
final class Version extends Command
{
    /**
     * @param non-negative-int $id
     */
    public function __construct(
        private readonly int $id,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $header = Header::asRequest(
            Opcode::Version,
            $this->id,
        );

        $header->write($writer);
    }

    public function parseResponse(Response $response): string
    {
        return $response->value ?? '';
    }
}
