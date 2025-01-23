<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<string>
 */
final class Version implements Command
{
    public function asRequest(): string
    {
        return "version\r\n";
    }
}
