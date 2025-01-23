<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text\Result;

use Amp\DeferredFuture;
use Thesis\Memcached\Internal\Protocol\Text\Result;

/**
 * @internal
 * @template-extends Result<\Throwable>
 */
final class Err extends Result
{
    public function __construct(
        private readonly \Throwable $exception,
    ) {}

    public function complete(DeferredFuture $deferred): void
    {
        $deferred->error($this->exception);
    }
}
