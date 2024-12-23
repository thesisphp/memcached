<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Typhoon\ByteOrder\ReadFrom;
use Typhoon\Memcached\Exception\KeyAlreadyExists;
use Typhoon\Memcached\Exception\KeyNotFound;
use Typhoon\Memcached\Exception\KeyNotStored;
use Typhoon\Memcached\Exception\MemcachedClientError;
use Typhoon\Memcached\Exception\MemcachedServerError;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class Response
{
    public function __construct(
        public readonly Header $header,
        public readonly ?string $extras = null,
        public readonly ?string $key = null,
        public readonly ?string $value = null,
    ) {}

    /**
     * @throws \Throwable
     */
    public static function read(ReadFrom $reader): self
    {
        $header = Header::read($reader);

        return new self(
            $header,
            value: $reader->read($header->totalBodyLength - $header->keyLength - $header->extrasLength),
        );
    }

    /**
     * @throws \Throwable
     */
    public function throwOnError(): void
    {
        $status = ResponseStatus::from($this->header->vbucketIdOrStatus);

        /** @var ?\Throwable $exception */
        $exception = match ($status) {
            ResponseStatus::KEY_EXISTS => new KeyAlreadyExists(),
            ResponseStatus::KEY_NOT_FOUND => new KeyNotFound(),
            ResponseStatus::NOT_STORED => new KeyNotStored(),
            ResponseStatus::AUTHENTICATION_ERROR,
            ResponseStatus::INVALID_ARGUMENTS,
            ResponseStatus::NON_NUMERIC,
            ResponseStatus::UNKNOWN_COMMAND,
            ResponseStatus::NOT_SUPPORTED,
            ResponseStatus::VALUE_TOO_LARGE,
            ResponseStatus::VBUCKET_ANOTHER_SERVER => new MemcachedClientError($status->describe()),
            ResponseStatus::TEMPORARY_FAILURE,
            ResponseStatus::INTERNAL_ERROR,
            ResponseStatus::BUSY,
            ResponseStatus::OUT_OF_MEMORY => new MemcachedServerError($status->describe()),
            ResponseStatus::NO_ERROR,
            ResponseStatus::AUTHENTICATION_CONTINUE => null,
        };

        if ($exception !== null) {
            throw $exception;
        }
    }
}
