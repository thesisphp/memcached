<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @template-covariant T
 */
interface Command
{
    /**
     * @return non-empty-string
     */
    public function asRequest(): string;
}
