<?php

declare(strict_types=1);

namespace Thesis\Memcached;

/**
 * @api
 */
final class Item
{
    /**
     * @param non-negative-int $flags
     * @param non-negative-int $casId
     */
    public function __construct(
        public readonly string|int $value,
        public readonly ?Expiration $expiration = null,
        public readonly int $flags = 0,
        public readonly int $casId = 0,
        public readonly int $initialValue = 0,
    ) {}
}
