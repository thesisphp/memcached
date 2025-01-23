<?php

declare(strict_types=1);

namespace Typhoon\Memcached;

use Amp\Cancellation;
use Amp\NullCancellation;
use Typhoon\Memcached\Exception\ConnectionIsClosed;
use Typhoon\Memcached\Exception\KeyAlreadyExists;
use Typhoon\Memcached\Exception\KeyNotFound;
use Typhoon\Memcached\Exception\KeyNotStored;
use Typhoon\Memcached\Exception\MemcachedClientError;
use Typhoon\Memcached\Internal\Protocol\Protocol;

/**
 * @api
 */
final class Client
{
    /**
     * @internal
     * @psalm-internal Typhoon\Memcached
     */
    public function __construct(
        private readonly Protocol $protocol,
    ) {}

    /**
     * @throws ConnectionIsClosed
     */
    public function version(Cancellation $cancellation = new NullCancellation()): string
    {
        return $this->protocol->version($cancellation);
    }

    /**
     * @psalm-param non-negative-int $level
     * @throws ConnectionIsClosed
     */
    public function verbosity(int $level, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->verbosity($level, $cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function quit(): void
    {
        $this->protocol->quit();
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function get(Key $key, Cancellation $cancellation = new NullCancellation()): ?Item
    {
        return $this->protocol->get($key, $cancellation);
    }

    /**
     * @param list<Key> $keys
     * @return iterable<non-empty-string, Item>
     * @throws ConnectionIsClosed
     */
    public function gets(array $keys, Cancellation $cancellation = new NullCancellation()): iterable
    {
        return $this->protocol->gets($keys, $cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function set(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->set($key, $item, $cancellation);
    }

    /**
     * Store this data, but only if the server doesn't already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyAlreadyExists
     */
    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->add($key, $item, $cancellation);
    }

    /**
     * Store this data, but only if the server does already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyNotFound
     */
    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->replace($key, $item, $cancellation);
    }

    /**
     * Add this data to an existing key after existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->append($key, $item, $cancellation);
    }

    /**
     * Add this data to an existing key before existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->prepend($key, $item, $cancellation);
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
        $this->protocol->cas($key, $item, $cancellation);
    }

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     * @throws KeyNotFound
     */
    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->protocol->incr($key, $delta, $cancellation);
    }

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     */
    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int
    {
        return $this->protocol->decr($key, $delta, $cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->delete($key, $cancellation);
    }

    /**
     * Used to update the expiration time of an existing item without fetching it.
     *
     * @throws ConnectionIsClosed
     */
    public function touch(Key $key, ?Expiration $expiration = null, Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->touch($key, $expiration, $cancellation);
    }

    /**
     * @return iterable<non-empty-string, Stat>
     * @throws ConnectionIsClosed
     */
    public function stats(Cancellation $cancellation = new NullCancellation()): iterable
    {
        return $this->protocol->stats($cancellation);
    }

    /**
     * @throws ConnectionIsClosed
     */
    public function flush(Cancellation $cancellation = new NullCancellation()): void
    {
        $this->protocol->flush($cancellation);
    }

    public function close(): void
    {
        $this->protocol->close();
    }
}
