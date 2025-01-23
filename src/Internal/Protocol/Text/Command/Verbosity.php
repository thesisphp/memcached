<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<void>
 */
final class Verbosity implements Command
{
    /**
     * @psalm-param non-negative-int $level
     */
    public function __construct(
        private readonly int $level,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("verbosity %d\r\n", $this->level);
    }
}
