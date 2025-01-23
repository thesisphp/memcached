<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary\Command;

use Thesis\ByteOrder\WriteTo;
use Thesis\Memcached\Internal\Protocol\Binary\Command;
use Thesis\Memcached\Internal\Protocol\Binary\Header;
use Thesis\Memcached\Internal\Protocol\Binary\Opcode;
use Thesis\Memcached\Internal\Protocol\Binary\Response;
use Thesis\Memcached\Stat;
use function Thesis\Memcached\Internal\isNotEmptyString;

/**
 * @internal
 * @template-extends Command<array<non-empty-string, Stat>>
 */
final class Stats extends Command
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
            Opcode::Stat,
            $this->id,
        );

        $header->write($writer);
    }

    public function parseResponse(Response $response): array
    {
        $stats = [];

        foreach ([$response, ...$response->responses] as $it) {
            if (isNotEmptyString($it->key) && isNotEmptyString($it->value)) {
                $stats[$it->key] = new Stat(
                    $it->key,
                    $it->value,
                );
            }
        }

        return $stats;
    }
}
