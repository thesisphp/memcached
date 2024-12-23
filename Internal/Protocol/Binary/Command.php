<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-covariant T
 */
abstract class Command implements Writable
{
    /**
     * @return non-negative-int
     */
    abstract public function id(): int;

    /**
     * @return T
     * @throws \Throwable
     */
    final public function parseResponse(Response $response)
    {
        $response->throwOnError();

        return $this->doParseResponse($response);
    }

    /**
     * @return T
     * @throws \Throwable
     */
    protected function doParseResponse(Response $response): mixed
    {
        return null;
    }
}
