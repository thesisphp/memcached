<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Text;

/**
 * @internal
 * @template-covariant T
 */
interface Command
{
    /**
     * @return non-empty-string
     */
    public function asRequest(): string;
}
