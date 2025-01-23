<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

use Amp\DeferredFuture;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template T
 * @psalm-inheritors Result\Ok|Result\Err
 */
abstract class Result
{
    /**
     * @param DeferredFuture<T> $deferred
     */
    abstract public function complete(DeferredFuture $deferred): void;

    final public static function ok(mixed $value): Result\Ok
    {
        return new Result\Ok($value);
    }

    final public static function err(\Throwable $e): Result\Err
    {
        return new Result\Err($e);
    }
}
