<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
enum Magic: int
{
    case REQUEST = 0x80;
    case RESPONSE = 0x81;
}
