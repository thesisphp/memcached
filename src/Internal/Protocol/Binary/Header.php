<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Binary;

use Typhoon\ByteOrder\ReadFrom;
use Typhoon\ByteOrder\WriteTo;
use Typhoon\ByteReader\NotEnoughBytes;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 */
final class Header
{
    /**
     * @param non-negative-int $opaque
     * @param non-negative-int $keyLength
     * @param non-negative-int $extrasLength
     * @param non-negative-int $totalBodyLength
     * @param non-negative-int $cas
     */
    public static function asRequest(
        Opcode $opcode,
        int $opaque,
        int $keyLength = 0,
        int $extrasLength = 0,
        int $totalBodyLength = 0,
        int $cas = 0,
    ): self {
        return new self(
            magic: Magic::REQUEST,
            opcode: $opcode,
            opaque: $opaque,
            keyLength: $keyLength,
            extrasLength: $extrasLength,
            totalBodyLength: $totalBodyLength,
            cas: $cas,
        );
    }

    /**
     * @param non-negative-int $opaque
     * @param non-negative-int $keyLength
     * @param non-negative-int $extrasLength
     * @param non-negative-int $totalBodyLength
     * @param non-negative-int $vbucketIdOrStatus
     * @param non-negative-int $cas
     */
    public function __construct(
        public readonly Magic $magic,
        public readonly Opcode $opcode,
        public readonly int $opaque,
        public readonly int $keyLength = 0,
        public readonly int $extrasLength = 0,
        public readonly int $vbucketIdOrStatus = 0,
        public readonly int $totalBodyLength = 0,
        public readonly int $cas = 0,
        public readonly DataType $dataType = DataType::RAW,
    ) {}

    /**
     * @throws \Throwable
     */
    public function write(WriteTo $writer): void
    {
        $writer
            ->writeUint8($this->magic->value)
            ->writeUint8($this->opcode->value)
            ->writeUint16($this->keyLength)
            ->writeUint8($this->extrasLength)
            ->writeUint8($this->dataType->value)
            ->writeUint16($this->vbucketIdOrStatus)
            ->writeUint32($this->totalBodyLength)
            ->writeUint32($this->opaque)
            ->writeUint64($this->cas);
    }

    /**
     * @throws NotEnoughBytes
     */
    public static function read(ReadFrom $reader): self
    {
        $magic = Magic::from($reader->readUint8());
        $opcode = Opcode::from($reader->readUint8());
        /** @var non-negative-int $keyLength */
        $keyLength = $reader->readUint16();
        /** @var non-negative-int $extrasLength */
        $extrasLength = $reader->readUint8();
        $dataType = DataType::from($reader->readUint8());
        /** @var non-negative-int $vbucketIdOrStatus */
        $vbucketIdOrStatus = $reader->readUint16();
        /** @var non-negative-int $totalBodyLength */
        $totalBodyLength = $reader->readUint32();
        /** @var non-negative-int $opaque */
        $opaque = $reader->readUint32();
        /** @var non-negative-int $cas */
        $cas = $reader->readUint64();

        return new self(
            magic: $magic,
            opcode: $opcode,
            opaque: $opaque,
            keyLength: $keyLength,
            extrasLength: $extrasLength,
            vbucketIdOrStatus: $vbucketIdOrStatus,
            totalBodyLength: $totalBodyLength,
            cas: $cas,
            dataType: $dataType,
        );
    }
}
