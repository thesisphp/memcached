<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Amp\DeferredFuture;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template T
 */
final class Completion
{
    /** @var callable(Response): T */
    private $parseResponse;

    /**
     * @param DeferredFuture<T> $future
     * @param callable(Response): T $parseResponse
     */
    public function __construct(
        private readonly DeferredFuture $future,
        callable $parseResponse,
    ) {
        $this->parseResponse = $parseResponse;
    }

    public function complete(Response $response): void
    {
        $this->future->complete(
            ($this->parseResponse)($response),
        );
    }

    public function error(\Throwable $e): void
    {
        $this->future->error($e);
    }
}
