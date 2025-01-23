<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

/**
 * @internal
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
     */
    abstract public function parseResponse(Response $response);
}
