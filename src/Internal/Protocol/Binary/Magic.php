<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 */
enum Magic: int
{
    case REQUEST = 0x80;
    case RESPONSE = 0x81;
}
