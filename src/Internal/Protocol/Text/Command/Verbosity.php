<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;

/**
 * @internal
 * @template-implements Command<void>
 */
final class Verbosity implements Command
{
    /**
     * @param non-negative-int $level
     */
    public function __construct(
        private readonly int $level,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("verbosity %d\r\n", $this->level);
    }
}
