<?php

declare(strict_types=1);

namespace Thesis\Memcached;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached\Exception\KeyIsInvalid;

#[CoversClass(Key::class)]
final class KeyTest extends TestCase
{
    public function testKeyIsEmpty(): void
    {
        self::expectException(KeyIsInvalid::class);
        self::expectExceptionMessage('The key is empty.');

        new Key('');
    }

    public function testKeyIsTooLong(): void
    {
        self::expectException(KeyIsInvalid::class);
        self::expectExceptionMessage(\sprintf('The key "%s" is too long.', $key = str_repeat('x', 251)));

        new Key($key);
    }

    public function testKeyContainsIllegalCharacter(): void
    {
        self::expectException(KeyIsInvalid::class);
        self::expectExceptionMessage('The key "x " contains illegal character " ".');

        new Key('x ');
    }

    public function testKeyCreated(): void
    {
        self::assertSame('x', (string) new Key('x'));
    }
}
