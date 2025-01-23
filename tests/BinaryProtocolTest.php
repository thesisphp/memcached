<?php

declare(strict_types=1);

namespace Thesis\Memcached;

use PHPUnit\Framework\Attributes\CoversClass;
use Thesis\Memcached\Internal\Protocol\Binary\BinaryProtocol;
use Thesis\Memcached\Internal\Protocol\ProtocolType;

#[CoversClass(BinaryProtocol::class)]
final class BinaryProtocolTest extends ClientTestCase
{
    protected function protocolType(): ProtocolType
    {
        return ProtocolType::BINARY;
    }

    public function testIncrementUnknownKey(): void
    {
        self::assertSame(0, $this->memcached->incr(new Key('x'), 1));
    }

    public function testDecrementUnknownKey(): void
    {
        self::assertSame(0, $this->memcached->decr(new Key('x'), 1));
    }

    public function testNoMemoryLeaks(): void
    {
        $protocol = ProtocolType::BINARY->connect(\Amp\Socket\connect($this->host));
        $protocol->flush();
        $ref = \WeakReference::create($protocol);

        unset($protocol);

        self::assertNull($ref->get());
    }
}
