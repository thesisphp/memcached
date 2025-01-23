<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Command;

use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Key;

/**
 * @internal
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
