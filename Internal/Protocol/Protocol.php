<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol;

use Amp\Cancellation;
use Amp\NullCancellation;
use Typhoon\Memcached\Exception\ConnectionIsClosed;
use Typhoon\Memcached\Exception\KeyAlreadyExists;
use Typhoon\Memcached\Exception\KeyNotFound;
use Typhoon\Memcached\Exception\KeyNotStored;
use Typhoon\Memcached\Exception\MemcachedClientError;
use Typhoon\Memcached\Expiration;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;
use Typhoon\Memcached\Stat;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
interface Protocol
{
    /**
     * @throws ConnectionIsClosed
     */
    public function version(Cancellation $cancellation = new NullCancellation()): string;

    /**
     * @psalm-param non-negative-int $level
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
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyAlreadyExists
     */
    public function add(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     * @throws KeyNotFound
     */
    public function replace(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function append(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
     * @throws KeyNotStored
     */
    public function prepend(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
     * @throws KeyAlreadyExists
     * @throws KeyNotFound
     */
    public function cas(Key $key, Item $item, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     * @throws KeyNotFound
     */
    public function incr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int;

    /**
     * @psalm-param non-negative-int $delta
     * @throws ConnectionIsClosed
     * @throws MemcachedClientError
     */
    public function decr(Key $key, int $delta, Cancellation $cancellation = new NullCancellation()): int;

    /**
     * @throws ConnectionIsClosed
     */
    public function delete(Key $key, Cancellation $cancellation = new NullCancellation()): void;

    /**
     * @throws ConnectionIsClosed
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
