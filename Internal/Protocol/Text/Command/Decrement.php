<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<int>
 */
final class Decrement implements Command
{
    /**
     * @psalm-param non-negative-int $delta
     */
    public function __construct(
        private readonly Key $key,
        private readonly int $delta,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("decr %s %d\r\n", $this->key, $this->delta);
    }
}
