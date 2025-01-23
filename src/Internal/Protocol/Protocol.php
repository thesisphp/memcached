<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol;

use Amp\Cancellation;
use Amp\NullCancellation;
use Thesis\Memcached\Exception\ConnectionIsClosed;
use Thesis\Memcached\Exception\KeyAlreadyExists;
use Thesis\Memcached\Exception\KeyNotFound;
use Thesis\Memcached\Exception\KeyNotStored;
use Thesis\Memcached\Exception\MemcachedClientError;
use Thesis\Memcached\Expiration;
use Thesis\Memcached\Item;
use Thesis\Memcached\Key;
use Thesis\Memcached\Stat;

/**
 * @internal
 */
interface Protocol
{
    /**
     * @throws ConnectionIsClosed
     */
    public function version(Cancellation $cancellation = new NullCancellation()): string;

    /**
     * @param non-negative-int $level
     * @throws ConnectionIsClosed
     */
    public function verbosity(int $level, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
     */
    public function quit(): void;

    /**
     * @throws ConnectionIsClosed
     */
    public function get(Key $key, Cancellation $cancellation = new NullCancellation()): ?Item;

    /**
     * @param list<Key> $keys
     * @return iterable<non-empty-string, Item>
     * @throws ConnectionIsClosed
     */
    public function gets(array $keys, Cancellation $cancellation = new NullCancellation()): iterable;

    /**
     * @throws ConnectionIsClosed
     */
    public function set(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Store this data, but only if the server doesn't already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyAlreadyExists
     */
    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Store this data, but only if the server does already hold data for this key.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyNotFound
     */
    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Add this data to an existing key after existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyNotFound
     */
    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Add this data to an existing key before existing data.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyNotFound
     */
    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Store this data but only if no one else has updated since I last fetched it.
     *
     * @throws ConnectionIsClosed
     * @throws KeyAlreadyExists
     * @throws KeyNotFound
     */
    public function cas(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     * @throws KeyNotFound
     */
    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int;

    /**
     * @param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     */
    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int;

    /**
     * @throws ConnectionIsClosed
     */
    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * Used to update the expiration time of an existing item without fetching it.
     *
     * @throws ConnectionIsClosed
     * @throws KeyNotFound
     */
    public function touch(Key $key, ?Expiration $expiration = null, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @return iterable<non-empty-string, Stat>
     * @throws ConnectionIsClosed
     */
    public function stats(Cancellation $cancellation = new NullCancellation()): iterable;

    /**
     * @throws ConnectionIsClosed
     */
    public function flush(Cancellation $cancellation = new NullCancellation()): void;

    public function close(): void;
}
