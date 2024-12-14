<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-implements Command<void>
 */
final class Delete implements Command
{
    public function __construct(
        private readonly Key $key,
    ) {}

    public function asRequest(): string
    {
        return \sprintf("delete %s\r\n", $this->key);
    }
}
