<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Stat;

/**
 * @internal
 * @template-implements Command<array<non-empty-string, Stat>>
 */
final class Stats implements Command
{
    public function asRequest(): string
    {
        return "stats\r\n";
    }
}
