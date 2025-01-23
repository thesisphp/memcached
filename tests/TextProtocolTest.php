<?php

declare(strict_types=1);

namespace Thesis\Memcached;

use PHPUnit\Framework\Attributes\CoversClass;
use Thesis\Memcached\Exception\KeyNotFound;
use Thesis\Memcached\Internal\Protocol\ProtocolType;
use Thesis\Memcached\Internal\Protocol\Text\TextProtocol;

#[CoversClass(TextProtocol::class)]
final class TextProtocolTest extends ClientTestCase
{
    protected function protocolType(): ProtocolType
    {
        return ProtocolType::TEXT;
    }

    public function testIncrementUnknownKey(): void
    {
        self::expectException(KeyNotFound::class);
        $this->memcached->incr(new Key('x'), 1);
    }

    public function testDecrementUnknownKey(): void
    {
        self::expectException(KeyNotFound::class);
        $this->memcached->decr(new Key('x'), 1);
    }

    public function testNoMemoryLeaks(): void
    {
        $protocol = ProtocolType::TEXT->connect(\Amp\Socket\connect($this->host));
        $protocol->flush();
        $ref = \WeakReference::create($protocol);

        unset($protocol);

        self::assertNull($ref->get());
    }
}
