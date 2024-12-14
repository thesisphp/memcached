<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Typhoon\ByteOrder\ReadFrom;

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
}
