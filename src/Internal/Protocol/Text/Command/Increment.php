<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-implements Command<int>
 */
final class Increment implements Command
{
    /**
     * @param non-negative-int $delta
     */
    public function __construct(
        private readonly Key $key,
        private readonly int $delta,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("incr %s %d\r\n", $this->key, $this->delta);
    }
}
