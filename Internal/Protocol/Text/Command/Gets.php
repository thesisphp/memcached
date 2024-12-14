<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<array<non-empty-string, Item>>
 */
final class Gets implements Command
{
    /**
     * @param non-empty-list<Key> $keys
     */
    public function __construct(
        private readonly array $keys,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("gets %s\r\n", implode(' ', array_map(\strval(...), $this->keys)));
    }
}
