<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @psalm-assert-if-true non-empty-string $value
 */
function isNotEmptyString(?string $value): bool
{
    return $value !== null && $value !== '';
}
