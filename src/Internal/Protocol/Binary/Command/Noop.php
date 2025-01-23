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
final class Noop extends Command
{
    /**
     * @param non-negative-int $id
     */
    public static function flush(int $id): self
    {
        return new self($id, Opcode::Flush);
    }

    /**
     * @param non-negative-int $id
     */
    public static function quit(int $id): self
    {
        return new self($id, Opcode::Quit);
    }

    /**
     * @param non-negative-int $id
     */
    private function __construct(
        private readonly int $id,
        private readonly Opcode $opcode,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function write(WriteTo $writer): void
    {
        $header = Header::asRequest(
            $this->opcode,
            $this->id,
        );

        $header->write($writer);
    }

    public function parseResponse(Response $response): void {}
}
