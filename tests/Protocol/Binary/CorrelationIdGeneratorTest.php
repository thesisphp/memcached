<?php

declare(strict_types=1);

namespace Thesis\Memcached\Protocol\Binary;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached\Internal\Protocol\Binary\CorrelationIdGenerator;

#[CoversClass(CorrelationIdGenerator::class)]
final class CorrelationIdGeneratorTest extends TestCase
{
    public function testNext(): void
    {
        $sequence = new CorrelationIdGenerator();

        self::assertSame(1, $sequence->next());
        self::assertSame(2, $sequence->next());
        self::assertSame(3, $sequence->next());
        self::assertSame(4, $sequence->next());
        self::assertSame(5, $sequence->next());
        self::assertSame(6, $sequence->next());
    }
}
