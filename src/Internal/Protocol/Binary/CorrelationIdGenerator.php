<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 * @phpstan-type CorrelationId = int<0, 4294967295>
 */
final class CorrelationIdGenerator
{
    /** @var int */
    private const MAX_CORRELATION_ID = (1 << 32) - 1;

    /** @var CorrelationId */
    private int $correlationId = 0;

    /**
     * @return CorrelationId
     */
    public function next(): int
    {
        if ($this->correlationId >= self::MAX_CORRELATION_ID) {
            $this->correlationId = 0;
        }

        return ++$this->correlationId;
    }
}
