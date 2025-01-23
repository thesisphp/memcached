<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Item;
use Thesis\Memcached\Key;

/**
 * @internal
 * @template-implements Command<array<non-empty-string, Item>>
 */
final class Get implements Command
{
    public function __construct(
        private readonly Key $key,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("get %s\r\n", $this->key);
    }
}
