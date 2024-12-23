<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

/**
 * @template-covariant T
 */
interface Command extends Writable
{
    /**
     * @return non-negative-int
     */
    public function id(): int;

    /**
     * @throws \Throwable
     * @return T
     */
    public function parseResponse(Response $response);
}
