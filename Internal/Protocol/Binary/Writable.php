<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Typhoon\ByteOrder\WriteTo;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
interface Writable
{
    /**
     * @throws \Throwable
     */
    public function write(WriteTo $writer): void;
}
