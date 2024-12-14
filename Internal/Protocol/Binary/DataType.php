<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
enum DataType: int
{
    case RAW = 0x00;
}
