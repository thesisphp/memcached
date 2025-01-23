<?php

declare(strict_types=1);

namespace Thesis\Memcached;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached\Exception\InvalidConfiguration;
use Thesis\Memcached\Internal\Protocol\ProtocolType;

#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    public function testConfigParsed(): void
    {
        $config = Config::fromString('tcp://guest:guest@localhost:11211');
        self::assertSame('localhost', $config->host);
        self::assertSame(11211, $config->port);
        self::assertSame('guest', $config->user);
        self::assertSame('guest', $config->password);
        self::assertSame(ProtocolType::TEXT, $config->protocolType);
    }

    public function testBinaryProtocolPreferred(): void
    {
        $config = Config::fromString('tcp://guest:guest@localhost:11211?proto=binary');
        self::assertSame('localhost', $config->host);
        self::assertSame(11211, $config->port);
        self::assertSame('guest', $config->user);
        self::assertSame('guest', $config->password);
        self::assertSame(ProtocolType::BINARY, $config->protocolType);
    }

    public function testEmptyDsn(): void
    {
        self::expectException(InvalidConfiguration::class);
        self::expectExceptionMessage('DSN string is empty.');
        Config::fromString('');
    }

    public function testIncorrectDsn(): void
    {
        self::expectException(InvalidConfiguration::class);
        self::expectExceptionMessage('The dsn "tcp://127.0.0.1:-1" is incorrect.');
        Config::fromString('tcp://127.0.0.1:-1');
    }

    public function testIncorrectProtocolType(): void
    {
        self::expectException(InvalidConfiguration::class);
        self::expectExceptionMessage('The protocol type "invalid" is incorrect.');
        Config::fromString('tcp://127.0.0.1:11211?proto=invalid');
    }
}
