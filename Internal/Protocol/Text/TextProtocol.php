<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Socket\Socket;
use Revolt\EventLoop;
use Typhoon\Memcached\Exception\ConnectionIsClosed;
use Typhoon\Memcached\Exception\KeyAlreadyExists;
use Typhoon\Memcached\Exception\KeyNotFound;
use Typhoon\Memcached\Exception\KeyNotStored;
use Typhoon\Memcached\Exception\MemcachedClientError;
use Typhoon\Memcached\Expiration;
use Typhoon\Memcached\Internal\Protocol\Protocol;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;
use Typhoon\Memcached\Stat;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class TextProtocol implements Protocol
{
    /** @var \SplQueue<array{DeferredFuture, Command}> */
    private readonly \SplQueue $queue;

    private bool $isAlive = false;

    private function __construct(
        private readonly Connection $connection,
    ) {
        /** @var \SplQueue<array{DeferredFuture, Command}> $queue */
        $queue = new \SplQueue();
        $this->queue = $queue;
    }

    public static function fromSocket(Socket $socket): self
    {
        return new self(new Connection($socket));
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function version(Cancellation $cancellation = new NullCancellation()): string
    {
        return $this->push(new Command\Version())->await($cancellation);
    }

    /**
     * @psalm-param non-negative-int $level
     * @throws ConnectionIsClosed
     */
    public function verbosity(int $level, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Verbosity($level))->await($cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function quit(): void
    {
        $this->push(new Command\Quit());
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function get(Key $key, Cancellation $cancellation = new NullCancellation()): ?Item
    {
        $items = $this->push(new Command\Get($key))->await($cancellation);

        return $items[(string) $key] ?? null;
    }

    /**
     * @param list<Key> $keys
     * @return iterable<non-empty-string, Item>
     * @throws ConnectionIsClosed
     */
    public function gets(array $keys, Cancellation $cancellation = new NullCancellation()): iterable
    {
        if (\count($keys) > 0) {
            yield from $this->push(new Command\Gets($keys))->await($cancellation);
        }
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function set(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::set($key, $item))->await($cancellation);
    }

    /**
     * Store this data, but only if the server doesn't already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::add($key, $item))->await($cancellation);
    }

    /**
     * Store this data, but only if the server does already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::replace($key, $item))->await($cancellation);
    }

    /**
     * Add this data to an existing key after existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::append($key, $item))->await($cancellation);
    }

    /**
     * Add this data to an existing key before existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::prepend($key, $item))->await($cancellation);
    }

    /**
     * Store this data but only if no one else has updated since I last fetched it.
     *
     * @throws ConnectionIsClosed
     * @throws KeyAlreadyExists
     * @throws KeyNotFound
     */
    public function cas(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Cas($key, $item))->await($cancellation);
    }

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     * @throws KeyNotFound
     */
    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->push(new Command\Increment($key, $delta))->await($cancellation);
    }

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     */
    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->push(new Command\Decrement($key, $delta))->await($cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Delete($key))->await($cancellation);
    }

    /**
     * Used to update the expiration time of an existing item without fetching it.
     *
     * @throws ConnectionIsClosed
     */
    public function touch(Key $key, ?Expiration $expiration = null, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Touch($key, $expiration ?: Expiration::zero()))->await($cancellation);
    }

    /**
     * @return iterable<non-empty-string, Stat>
     * @throws ConnectionIsClosed
     */
    public function stats(Cancellation $cancellation = new NullCancellation()): iterable
    {
        yield from $this->push(new Command\Stats())->await($cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function flush(Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\FlushAll())->await($cancellation);
    }

    public function close(): void
    {
        $this->isAlive = false;
        $this->connection->close();
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @template T
     * @param Command<T> $command
     * @return Future<T>
     * @throws ConnectionIsClosed
     */
    private function push(Command $command): Future
    {
        if ($this->connection->isClosed()) {
            throw new ConnectionIsClosed();
        }

        if (!$this->isAlive) {
            $this->ioLoop();
        }

        /** @psalm-var DeferredFuture<T> $deferred */
        $deferred = new DeferredFuture();
        $this->queue->push([$deferred, $command]);

        $this->connection->reference();

        try {
            $this->connection->write($command->asRequest());
        } catch (\Throwable $e) {
            $deferred->error($e);
        } finally {
            if ($command instanceof Command\Quit) {
                $this->close();
            }
        }

        return $deferred->getFuture();
    }

    private function ioLoop(): void
    {
        $this->isAlive = true;

        $isAlive = &$this->isAlive;
        $connection = &$this->connection;
        $queue = $this->queue;

        EventLoop::queue(static function () use (&$isAlive, &$connection, $queue): void {
            /** @psalm-suppress RedundantCondition */
            while ($isAlive) {
                while ($response = $connection->read()) {
                    [$deferred, $command] = $queue->shift();

                    try {
                        $result = $response->parse($command);
                        $result->complete($deferred);
                    } catch (\Throwable $e) {
                        $deferred->error($e);
                    }

                    if ($queue->isEmpty()) {
                        $connection->unreference();
                    }
                }
            }
        });
    }
}
