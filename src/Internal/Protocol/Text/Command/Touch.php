<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Expiration;
use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<void>
 */
final class Touch implements Command
{
    public function __construct(
        private readonly Key $key,
        private readonly Expiration $expiration,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("touch %s %d\r\n", $this->key, $this->expiration->value);
    }
}
