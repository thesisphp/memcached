<?php

declare(strict_types=1);

namespace Typhoon\Memcached;

/**
 * @api
 */
final class Stat
{
    /**
     * @param non-empty-string $key
     * @param non-empty-string $value
     */
    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {}
}
