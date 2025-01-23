<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

use Amp\ByteStream\ResourceStream;
use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Amp\Socket\Socket;
use Revolt\EventLoop;
use Thesis\AmpBridge\ReaderWriter as AmpReaderWriter;
use Thesis\ByteBuffer\BufferedReaderWriter;
use Thesis\ByteReaderWriter\ReaderWriter;
use Thesis\Memcached\Exception\ConnectionIsClosed;
use Thesis\Memcached\Exception\WriteIsFailed;

/**
 * @internal
 */
final class Connection
{
    private readonly ReaderWriter $buffer;

    private readonly Socket $socket;

    /** @var ConcurrentIterator<Response> */
    private readonly ConcurrentIterator $iterator;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
        $this->buffer = $buffer = new ReaderWriter(new BufferedReaderWriter(new AmpReaderWriter($socket)));

        /** @var Queue<Response> $queue */
        $queue = new Queue();
        $this->iterator = $queue->iterate();

        EventLoop::queue(static function () use ($buffer, $queue, $socket): void {
            while (!$socket->isClosed()) {
                try {
                    $queue->push(Response::read($buffer));
                } catch (\Throwable $e) {
                    $queue->error($e);
                }
            }

            if (!$queue->isComplete()) {
                $queue->complete();
            }

            $socket->close();
        });
    }

    /**
     * @throws ConnectionIsClosed
     * @throws WriteIsFailed
     */
    public function write(Writable $writable): void
    {
        if ($this->socket->isClosed()) {
            throw new ConnectionIsClosed();
        }

        try {
            $writable->write($this->buffer);
            $this->buffer->flush();
        } catch (\Throwable $e) {
            throw new WriteIsFailed($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function read(): ?Response
    {
        if (!$this->iterator->continue()) {
            return null;
        }

        return $this->iterator->getValue();
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

    public function close(): void
    {
        if (!$this->socket->isClosed()) {
            $this->socket->close();
        }
    }
}
