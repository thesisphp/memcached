<?php

declare(strict_types=1);

namespace Thesis\Memcached\Exception;

use Thesis\Memcached\MemcachedException;

/**
 * @api
 */
final class InvalidConfiguration extends MemcachedException
{
    public static function emptyDSN(): self
    {
        return new self('DSN string is empty.');
    }

    public static function incorrectDSN(string $dsn): self
    {
        return new self(\sprintf('The dsn "%s" is incorrect.', $dsn));
    }

    public static function incorrectProtocolType(string $protocolType): self
    {
        return new self(\sprintf('The protocol type "%s" is incorrect.', $protocolType));
    }
}
