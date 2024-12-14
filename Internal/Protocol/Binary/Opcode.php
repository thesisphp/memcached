<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
enum Opcode: int
{
    case Get = 0x00;
    case Set = 0x01;
    case Add = 0x02;
    case Replace = 0x03;
    case Delete = 0x04;
    case Increment = 0x05;
    case Decrement = 0x06;
    case Quit = 0x07;
    case Flush = 0x08;
    case Stat = 0x10;
    case Noop = 0x0A;
    case Version = 0x0B;
    case GetKQ = 0x0D;
    case Append = 0x0E;
    case Prepend = 0x0F;
    case Touch = 0x1C;
    case Auth = 0x21;
    case Verbosity = 0x1B;
}
