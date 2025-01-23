<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal;

/**
 * @internal
 * @phpstan-assert-if-true non-empty-string $value
 */
function isNotEmptyString(?string $value): bool
{
    return $value !== null && $value !== '';
}
