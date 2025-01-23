<?php

declare(strict_types=1);

namespace Thesis\Memcached\Internal\Protocol\Binary;

/**
 * @internal
 */
enum ResponseStatus: int
{
    case NO_ERROR = 0x0000;
    case KEY_NOT_FOUND = 0x0001;
    case KEY_EXISTS = 0x0002;
    case VALUE_TOO_LARGE = 0x0003;
    case INVALID_ARGUMENTS = 0x0004;
    case NOT_STORED = 0x0005;
    case NON_NUMERIC = 0x0006;
    case VBUCKET_ANOTHER_SERVER = 0x0007;
    case AUTHENTICATION_ERROR = 0x0008;
    case AUTHENTICATION_CONTINUE = 0x0009;
    case UNKNOWN_COMMAND = 0x0081;
    case OUT_OF_MEMORY = 0x0082;
    case NOT_SUPPORTED = 0x0083;
    case INTERNAL_ERROR = 0x0084;
    case BUSY = 0x0085;
    case TEMPORARY_FAILURE = 0x0086;

    /**
     * @return non-empty-string
     */
    public function describe(): string
    {
        return match ($this) {
            self::NO_ERROR, self::AUTHENTICATION_CONTINUE => 'ok',
            self::KEY_NOT_FOUND => 'Key not found',
            self::KEY_EXISTS => 'Key exists',
            self::VALUE_TOO_LARGE => 'Value too large',
            self::INVALID_ARGUMENTS => 'Invalid arguments',
            self::NOT_STORED => 'Not stored',
            self::NON_NUMERIC => 'Non numeric',
            self::VBUCKET_ANOTHER_SERVER => 'VBucket point to another server',
            self::AUTHENTICATION_ERROR => 'Authentication error',
            self::UNKNOWN_COMMAND => 'Unknown command',
            self::OUT_OF_MEMORY => 'Out of memory',
            self::NOT_SUPPORTED => 'Not supported',
            self::INTERNAL_ERROR => 'Internal error',
            self::BUSY => 'Server is busy',
            self::TEMPORARY_FAILURE => 'Temporary failure',
        };
    }
}
