<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

use Thesis\ByteOrder\WriteTo;

/**
 * @internal
 */
interface Writable
{
    /**
     * @throws \Throwable
     */
    public function write(WriteTo $writer): void;
}
