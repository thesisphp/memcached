<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Stat;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<array<non-empty-string, Stat>>
 */
final class Stats implements Command
{
    public function asRequest(): string
    {
        return "stats\r\n";
    }
}
