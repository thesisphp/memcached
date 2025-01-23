<?php

declare(strict_types=1);

namespace Typhoon\Memcached;

use Amp\Cancellation;
use Amp\CancelledException;
use Amp\NullCancellation;
use Amp\Socket;
use Typhoon\Memcached\Exception\ConnectionIsFailed;
use Typhoon\Memcached\Exception\InvalidConfiguration;

/**
 * @api
 * @throws ConnectionIsFailed
 * @throws InvalidConfiguration
 */
function connect(
    Config|string $config,
    ?Socket\ConnectContext $context = null,
    ?Socket\SocketConnector $connector = null,
    Cancellation $cancellation = new NullCancellation(),
): Client {
    $connector ??= Socket\socketConnector();

    if (\is_string($config)) {
        $config = Config::fromString($config);
    }

    try {
        return new Client(
            $config->protocolType->connect(
                $connector->connect($config->uri(), $context, $cancellation),
            ),
        );
    } catch (Socket\SocketException|CancelledException $e) {
        throw new ConnectionIsFailed($e->getMessage(), $e->getCode(), $e);
    }
}
