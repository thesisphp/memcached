<?php

declare(strict_types=1);

namespace Thesis\Memcached;

use Amp\Socket\ConnectContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached;
use Thesis\Memcached\Exception\KeyAlreadyExists;
use function Amp\delay;

#[CoversClass(Client::class)]
abstract class ClientTestCase extends TestCase
{
    protected Client $memcached;

    protected string $host;

    protected function setUp(): void
    {
        parent::setUp();

        $host = getenv('THESIS_MEMCACHED_HOST');

        if (!\is_string($host) || $host === '') {
            self::markTestSkipped('THESIS_MEMCACHED_HOST is not set.');
        }

        $this->host = \sprintf('%s?proto=%s', $host, $this->protocolType()->value);

        $this->memcached = Memcached\connect($this->host, (new ConnectContext())->withTcpNoDelay());
        $this->memcached->flush();
    }

    final public function testSet(): void
    {
        $this->memcached->set(new Key('x'), new Item('y'));
        self::assertSame('y', $this->memcached->get(new Key('x'))?->value);
    }

    final public function testAddExistingKey(): void
    {
        $this->memcached->set(new Key('x'), new Item('y'));
        self::assertSame('y', $this->memcached->get(new Key('x'))?->value);
        self::expectException(KeyAlreadyExists::class);
        $this->memcached->add(new Key('x'), new Item('z'));
    }

    final public function testAddUnknownKey(): void
    {
        $this->memcached->add(new Key('x'), new Item('z'));
        self::assertSame('z', $this->memcached->get(new Key('x'))?->value);
    }

    final public function testReplace(): void
    {
        $this->memcached->set(new Key('x'), new Item('y'));
        self::assertSame('y', $this->memcached->get(new Key('x'))?->value);
        $this->memcached->replace(new Key('x'), new Item('z'));
        self::assertSame('z', $this->memcached->get(new Key('x'))?->value);
    }

    final public function testAppend(): void
    {
        $this->memcached->set(new Key('x'), new Item('y'));
        $this->memcached->append(new Key('x'), new Item('z'));
        self::assertSame('yz', $this->memcached->get(new Key('x'))?->value);
    }

    final public function testPrepend(): void
    {
        $this->memcached->set(new Key('x'), new Item('y'));
        $this->memcached->prepend(new Key('x'), new Item('z'));
        self::assertSame('zy', $this->memcached->get(new Key('x'))?->value);
    }

    final public function testIncrement(): void
    {
        $this->memcached->set(new Key('x'), new Item('1'));
        self::assertSame(3, $this->memcached->incr(new Key('x'), 2));
    }

    abstract public function testIncrementUnknownKey(): void;

    final public function testDecrement(): void
    {
        $this->memcached->set(new Key('x'), new Item('3'));
        self::assertSame(1, $this->memcached->decr(new Key('x'), 2));
    }

    abstract public function testDecrementUnknownKey(): void;

    final public function testDelete(): void
    {
        $this->memcached->set(new Key('x'), new Item('3'));
        $this->memcached->delete(new Key('x'));
        self::assertNull($this->memcached->get(new Key('x')));
    }

    final public function testGets(): void
    {
        $this->memcached->set(new Key('a'), new Item('b'));
        $this->memcached->set(new Key('x'), new Item('y'));
        $this->memcached->set(new Key('foo'), new Item('bar'));

        $items = [...$this->memcached->gets([
            new Key('a'),
            new Key('x'),
            new Key('bar'),
            new Key('foo'),
        ])];

        self::assertCount(3, $items);
        self::assertSame('b', $items['a']->value ?? null);
        self::assertSame('y', $items['x']->value ?? null);
        self::assertSame('bar', $items['foo']->value ?? null);
    }

    final public function testFlush(): void
    {
        $this->memcached->set(new Key('a'), new Item('b'));
        self::assertSame('b', $this->memcached->get(new Key('a'))?->value);
        $this->memcached->flush();
        self::assertNull($this->memcached->get(new Key('a')));
    }

    final public function testExpired(): void
    {
        $this->memcached->set(new Key('a'), new Item('b', Expiration::fromSeconds(1)));
        self::assertSame('b', $this->memcached->get(new Key('a'))?->value);
        delay(1.2);
        self::assertNull($this->memcached->get(new Key('a')));
    }

    final public function testTouch(): void
    {
        $this->memcached->set(new Key('a'), new Item('b', Expiration::fromSeconds(1)));
        self::assertSame('b', $this->memcached->get(new Key('a'))?->value);
        $this->memcached->touch(new Key('a'), Expiration::fromSeconds(10));
        delay(1.2);
        self::assertSame('b', $this->memcached->get(new Key('a'))?->value);
    }

    final public function testCas(): void
    {
        $this->memcached->set(new Key('a'), new Item('b'));

        $items = [...$this->memcached->gets([new Key('a')])];

        self::assertCount(1, $items);
        $item = $items['a'] ?? self::fail('Item by key "a" was not set.');
        self::assertSame('b', $item->value);

        $this->memcached->cas(new Key('a'), new Item('c', casId: $item->casId));
        self::assertSame('c', $this->memcached->get(new Key('a'))?->value);

        self::expectException(KeyAlreadyExists::class);
        $this->memcached->cas(new Key('a'), new Item('d', casId: $item->casId));
    }

    final public function testStats(): void
    {
        $stats = [...$this->memcached->stats()];
        self::assertSame('64', $stats['pointer_size']->value ?? null);
    }

    abstract protected function protocolType(): Internal\Protocol\ProtocolType;

    abstract public function testNoMemoryLeaks(): void;
}
