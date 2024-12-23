<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
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
        $header = new Header(
            Magic::REQUEST,
            Opcode::Verbosity,
            $this->id,
            extrasLength: 4,
        );

        $header->write($writer);

        $writer->writeUint32($this->verbosityLevel);
    }
}
