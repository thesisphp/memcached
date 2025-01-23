<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Expiration;
use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Key;

/**
 * @internal
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
