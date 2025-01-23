<?php

declare(strict_types=1);

namespace Thesis\Memcached\Exception;

use Thesis\Memcached\MemcachedException;

/**
 * @api
 */
final class ConnectionIsFailed extends MemcachedException
{
    public static function emptyHosts(): self
    {
        return new self('Connection hosts is empty. You must provide at least one.');
    }

    /**
     * @param array<non-empty-string, \Throwable> $hostsExceptions
     */
    public static function fromHostsExceptions(array $hostsExceptions): self
    {
        return new self(
            vsprintf('%d of %d servers are down: %s', [
                \count($hostsExceptions),
                \count($hostsExceptions),
                implode('; ', array_map(static fn(string $host, \Throwable $exception): string => "{$host}: {$exception->getMessage()}", array_keys($hostsExceptions), array_values($hostsExceptions))),
            ]),
        );
    }
}
