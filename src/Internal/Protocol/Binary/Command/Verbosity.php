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
 * @template-extends Command<void>
 */
final class Verbosity extends Command
{
    /**
     * @param non-negative-int $id
     * @param non-negative-int $verbosityLevel
     */
    public function __construct(
        private readonly int $id,
        private readonly int $verbosityLevel,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $header = Header::asRequest(
            Opcode::Verbosity,
            $this->id,
            extrasLength: 4,
        );

        $header->write($writer);

        $writer->writeUint32($this->verbosityLevel);
    }

    public function parseResponse(Response $response): void {}
}
