<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text;

use Amp\DeferredFuture;

/**
 * @internal
 * @template T
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
