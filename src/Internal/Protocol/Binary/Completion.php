<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

use Amp\DeferredFuture;

/**
 * @internal
 * @template T
 */
final class Completion
{
    /**
     * @param DeferredFuture<T> $future
     * @param Command<T> $command
     */
    public function __construct(
        private readonly DeferredFuture $future,
        private readonly Command $command,
    ) {}

    public function complete(Response $response): void
    {
        try {
            $response->throwOnError();

            $this->future->complete(
                $this->command->parseResponse($response),
            );
        } catch (\Throwable $e) {
            $this->future->error($e);
        }
    }

    public function error(\Throwable $e): void
    {
        $this->future->error($e);
    }
}
