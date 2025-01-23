<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Result;

use Amp\DeferredFuture;
use Thesis\Memcached\Internal\Protocol\Text\Result;

/**
 * @internal
 * @template-extends Result<mixed>
 */
final class Ok extends Result
{
    public function __construct(
        private readonly mixed $value,
    ) {}

    public function complete(DeferredFuture $deferred): void
    {
        $deferred->complete($this->value);
    }
}
