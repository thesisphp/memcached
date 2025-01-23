<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol;

use Amp\Socket\Socket;
use Thesis\Memcached\Internal\Protocol\Binary\BinaryProtocol;
use Thesis\Memcached\Internal\Protocol\Text\TextProtocol;

/**
 * @internal
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
