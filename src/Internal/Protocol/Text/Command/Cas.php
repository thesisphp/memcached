<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<void>
 */
final class Cas implements Command
{
    public function __construct(
        private readonly Key $key,
        private readonly Item $item,
    ) {}

    public function asRequest(): string
    {
        return \sprintf(
            "cas %s %d %d %d %d\r\n%s\r\n",
            $this->key,
            $this->item->flags,
            $this->item->expiration?->value ?? 0,
            \strlen((string) $this->item->value),
            $this->item->casId,
            $this->item->value,
        );
    }
}
