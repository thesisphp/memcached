<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol;

use Amp\Socket\Socket;
use Typhoon\Memcached\Internal\Protocol\Binary\BinaryProtocol;
use Typhoon\Memcached\Internal\Protocol\Text\TextProtocol;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
enum ProtocolType: string
{
    case BINARY = 'binary';
    case TEXT = 'text';

    public function connect(Socket $socket): Protocol
    {
        return match ($this) {
            self::TEXT => TextProtocol::fromSocket($socket),
            self::BINARY => BinaryProtocol::fromSocket($socket),
        };
    }
}
