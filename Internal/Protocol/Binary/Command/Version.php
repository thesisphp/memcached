<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary\Command;

use Typhoon\ByteOrder\WriteTo;
use Typhoon\Memcached\Internal\Protocol\Binary\Command;
use Typhoon\Memcached\Internal\Protocol\Binary\Header;
use Typhoon\Memcached\Internal\Protocol\Binary\Magic;
use Typhoon\Memcached\Internal\Protocol\Binary\Opcode;
use Typhoon\Memcached\Internal\Protocol\Binary\Response;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
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
        $header = new Header(
            Magic::REQUEST,
            Opcode::Version,
            $this->id,
        );

        $header->write($writer);
    }

    protected function doParseResponse(Response $response): string
    {
        return $response->value ?? '';
    }
}
