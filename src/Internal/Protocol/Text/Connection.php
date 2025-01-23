<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

use Amp\ByteStream\ResourceStream;
use Amp\ByteStream\StreamException;
use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Amp\Socket\Socket;
use Revolt\EventLoop;
use Typhoon\Memcached\Exception\ConnectionIsClosed;
use Typhoon\Memcached\Exception\WriteIsFailed;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class Connection
{
    /** @var ConcurrentIterator<Response> */
    private readonly ConcurrentIterator $iterator;

    private readonly Socket $socket;

    public function __construct(Socket $socket)
    {
        /** @psalm-var Queue<Response> $queue */
        $queue = new Queue();
        $this->iterator = $queue->iterate();
        $this->socket = $socket;

        EventLoop::queue(static function () use ($queue, $socket): void {
            $parser = new Parser($queue->push(...));

            while (($chunk = $socket->read()) !== null) {
                if ($chunk !== '') {
                    try {
                        $parser->push($chunk);
                    } catch (\Throwable $e) {
                        $queue->error($e);
                    }
                }
            }

            $socket->close();
            $queue->complete();
        });
    }

    public function read(): ?Response
    {
        if (!$this->iterator->continue()) {
            return null;
        }

        return $this->iterator->getValue();
    }

    /**
     * @throws ConnectionIsClosed
     * @throws WriteIsFailed
     */
    public function write(string $req): void
    {
        if ($this->socket->isClosed()) {
            throw new ConnectionIsClosed();
        }

        try {
            $this->socket->write($req);
        } catch (StreamException $e) {
            throw new WriteIsFailed($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function reference(): void
    {
        if ($this->socket instanceof ResourceStream) {
            $this->socket->reference();
        }
    }

    public function unreference(): void
    {
        if ($this->socket instanceof ResourceStream) {
            $this->socket->unreference();
        }
    }

    public function isClosed(): bool
    {
        return $this->socket->isClosed();
    }

    public function close(): void
    {
        if (!$this->socket->isClosed()) {
            $this->socket->close();
        }
    }
}
