<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class Response
{
    private function __construct(
        private readonly mixed $response = null,
        private readonly ?\Throwable $exception = null,
    ) {}

    /**
     * @throws \Throwable
     */
    public function parse(Command $_command): Result
    {
        return match (true) {
            $this->exception !== null => Result::err($this->exception),
            default => Result::ok($this->response),
        };
    }

    public static function ok(mixed $value = null): self
    {
        return new self(response: $value);
    }

    public static function error(\Throwable $e): self
    {
        return new self(exception: $e);
    }
}
