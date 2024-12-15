<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\DisposedException;
use Amp\Pipeline\Queue;
use Amp\Socket\Socket;
use Revolt\EventLoop;
use Typhoon\Memcached\Exception\ConnectionIsClosed;
use Typhoon\Memcached\Exception\WriteIsFailed;
use Typhoon\Memcached\Expiration;
use Typhoon\Memcached\Internal\Protocol\Protocol;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class BinaryProtocol implements Protocol
{
    private readonly Connection $connection;

    private readonly CorrelationIdGenerator $sequence;

    /** @var Queue<array{Completion, Request}> */
    private readonly Queue $queue;

    /** @var array<non-negative-int, Completion> */
    private array $pending = [];

    private bool $running = true;

    private function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->sequence = new CorrelationIdGenerator();

        /** @var Queue<array{Completion, Request}> $queue */
        $queue = new Queue();
        $this->queue = $queue;

        EventLoop::queue($this->sendRequests(...), $queue->iterate());
        EventLoop::queue($this->resolveCompletions(...));
    }

    public static function fromSocket(Socket $socket): self
    {
        return new self(new Connection($socket));
    }

    /**
     * @throws ConnectionIsClosed
     * @throws WriteIsFailed
     */
    public function version(Cancellation $cancellation = new NullCancellation()): string
    {
        return $this->queueRequest(Request::version())->await($cancellation);
    }

    public function verbosity(int $level, Cancellation $cancellation = new NullCancellation()): void
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function quit(): void
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function get(Key $key, Cancellation $cancellation = new NullCancellation()): ?Item
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function gets(array $keys, Cancellation $cancellation = new NullCancellation()): iterable
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function set(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::set($key, $item))->await($cancellation);
    }

    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::add($key, $item))->await($cancellation);
    }

    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::replace($key, $item))->await($cancellation);
    }

    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::append($key, $item))->await($cancellation);
    }

    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::prepend($key, $item))->await($cancellation);
    }

    public function cas(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::set($key, $item))->await($cancellation);
    }

    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->queueRequest(Request::increment($key, new Item($delta)))->await($cancellation);
    }

    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->queueRequest(Request::decrement($key, new Item($delta)))->await($cancellation);
    }

    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::delete($key))->await($cancellation);
    }

    public function touch(Key $key, ?Expiration $expiration = null, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::touch($key, new Item('', expiration: $expiration)))->await($cancellation);
    }

    public function stats(Cancellation $cancellation = new NullCancellation()): iterable
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function flush(Cancellation $cancellation = new NullCancellation()): void
    {
        $this->queueRequest(Request::flush())->await($cancellation);
    }

    /**
     * @template T
     * @param Request<T> $request
     * @return Future<T>
     * @throws ConnectionIsClosed
     */
    private function queueRequest(Request $request): Future
    {
        /** @var DeferredFuture<T> $deferred */
        $deferred = new DeferredFuture();

        $request = $request->withId($this->sequence->next());

        try {
            $this->queue->push([
                new Completion($deferred, $request->responseParser()),
                $request,
            ]);
        } catch (DisposedException $e) {
            throw new ConnectionIsClosed($e->getMessage(), previous: $e);
        }

        return $deferred->getFuture();
    }

    /**
     * @param ConcurrentIterator<array{Completion, Request}> $iterator
     */
    private function sendRequests(ConcurrentIterator $iterator): void
    {
        while ($this->running) {
            $this->connection->unreference();

            while ($iterator->continue()) {
                $this->connection->reference();

                [$completion, $request] = $iterator->getValue();

                $this->pending[$request->id] = $completion;

                try {
                    $this->connection->write($request);
                } catch (\Throwable $e) {
                    $completion->error($e);

                    foreach ($iterator->getIterator() as [$completion]) {
                        $completion->error($e);
                    }

                    $this->running = false;
                }
            }
        }
    }

    private function resolveCompletions(): void
    {
        while ($this->running) {
            while (($response = $this->connection->read()) !== null) {
                $id = $response->header->opaque;

                $completion = $this->pending[$id] ?? null;
                $completion?->complete($response);

                unset($this->pending[$id]);
            }
        }
    }

    public function close(): void
    {
        $this->running = false;
        $this->connection->close();
    }
}
