<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;

/**
 * @internal
 * @template-implements Command<void>
 */
final class Quit implements Command
{
    public function asRequest(): string
    {
        return "quit\r\n";
    }
}
