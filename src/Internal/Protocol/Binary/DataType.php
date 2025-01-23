<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 */
enum DataType: int
{
    case RAW = 0x00;
}
