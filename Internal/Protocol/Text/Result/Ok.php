<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Result;

use Amp\DeferredFuture;
use Typhoon\Memcached\Internal\Protocol\Text\Result;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
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
