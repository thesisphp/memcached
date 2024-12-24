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
use Typhoon\Memcached\Exception\KeyNotFound;
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

    /** @var Queue<array{Completion, Command}> */
    private readonly Queue $queue;

    /** @var array<non-negative-int, Completion> */
    private array $pending = [];

    private bool $running = true;

    private function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->sequence = new CorrelationIdGenerator();

        /** @var Queue<array{Completion, Command}> $queue */
        $queue = new Queue();
        $this->queue = $queue;

        EventLoop::queue($this->sendCommands(...), $queue->iterate());
        EventLoop::queue($this->resolveCompletions(...));
    }

    public static function fromSocket(Socket $socket): self
    {
        return new self(new Connection($socket));
    }

    public function version(Cancellation $cancellation = new NullCancellation()): string
    {
        return $this->push(new Command\Version($this->sequence->next()))->await($cancellation);
    }

    public function verbosity(int $level, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Verbosity($this->sequence->next(), $level))->await($cancellation);
    }

    public function quit(): void
    {
        $this->push(Command\Noop::quit($this->sequence->next()))->await();
    }

    public function get(Key $key, Cancellation $cancellation = new NullCancellation()): ?Item
    {
        try {
            return $this->push(new Command\Get($this->sequence->next(), $key))->await($cancellation);
        } catch (KeyNotFound) {
            return null;
        }
    }

    public function gets(array $keys, Cancellation $cancellation = new NullCancellation()): iterable
    {
        if (\count($keys) > 0) {
            yield from $this->push(new Command\Gets($this->sequence->next(), $keys))->await($cancellation);
        }
    }

    public function set(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::set($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::add($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::replace($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Change::append($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Change::prepend($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function cas(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Store::set($this->sequence->next(), $key, $item))->await($cancellation);
    }

    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->push(Command\IncrDecr::incr($this->sequence->next(), $key, $delta))->await($cancellation);
    }

    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->push(Command\IncrDecr::decr($this->sequence->next(), $key, $delta))->await($cancellation);
    }

    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Delete($this->sequence->next(), $key))->await($cancellation);
    }

    public function touch(Key $key, ?Expiration $expiration = null, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(new Command\Touch($this->sequence->next(), $key, $expiration ?: Expiration::fromSeconds(0)))->await($cancellation);
    }

    public function stats(Cancellation $cancellation = new NullCancellation()): iterable
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }

    public function flush(Cancellation $cancellation = new NullCancellation()): void
    {
        $this->push(Command\Noop::flush($this->sequence->next()))->await($cancellation);
    }

    /**
     * @template T
     * @param Command<T> $command
     * @return Future<T>
     * @throws ConnectionIsClosed
     */
    private function push(Command $command): Future
    {
        /** @var DeferredFuture<T> $deferred */
        $deferred = new DeferredFuture();

        try {
            $this->queue->push([
                new Completion($deferred, $command),
                $command,
            ]);
        } catch (DisposedException $e) {
            throw new ConnectionIsClosed($e->getMessage(), previous: $e);
        }

        return $deferred->getFuture();
    }

    /**
     * @param ConcurrentIterator<array{Completion, Command}> $iterator
     */
    private function sendCommands(ConcurrentIterator $iterator): void
    {
        while ($this->running) {
            $this->connection->unreference();

            while ($iterator->continue()) {
                $this->connection->reference();

                [$completion, $command] = $iterator->getValue();

                $this->pending[$command->id()] = $completion;

                try {
                    $this->connection->write($command);
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
        while ($this->running && ($response = $this->connection->read()) !== null) {
            $id = $response->header->opaque;

            $completion = $this->pending[$id] ?? null;
            $completion?->complete($response);

            unset($this->pending[$id]);
        }
    }

    public function close(): void
    {
        $this->running = false;
        $this->connection->close();
    }
}
