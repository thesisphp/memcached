<?php

declare(strict_types=1);

namespace Thesis\Memcached;

/**
 * @api
 */
final class Expiration
{
    /**
     * @param non-negative-int $value
     */
    private function __construct(
        public readonly int $value,
    ) {}

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * @param non-negative-int $seconds
     */
    public static function fromSeconds(int $seconds): self
    {
        return new self($seconds);
    }

    public static function fromDateTime(\DateTimeInterface $time): self
    {
        /** @psalm-var non-negative-int $timestamp */
        $timestamp = $time->getTimestamp();

        return new self($timestamp);
    }

    public static function fromInterval(\DateInterval $interval): self
    {
        return self::fromDateTime((new \DateTimeImmutable('NOW'))->add($interval));
    }
}
