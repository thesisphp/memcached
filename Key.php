<?php

declare(strict_types=1);

namespace Typhoon\Memcached;

use Typhoon\Memcached\Exception\KeyIsInvalid;

/**
 * @api
 */
final class Key implements \Stringable
{
    /** @var positive-int */
    private const KEY_MAX_LENGTH = 250;

    /** @var non-empty-string */
    private readonly string $value;

    /**
     * @throws KeyIsInvalid
     */
    public function __construct(string $value)
    {
        if ($value === '') {
            throw KeyIsInvalid::keyIsEmpty();
        }

        if (\strlen($value) > self::KEY_MAX_LENGTH) {
            throw KeyIsInvalid::tooLong($value);
        }

        for ($i = 0; $i < \strlen($value); ++$i) {
            $code = \ord($value[$i]);

            // The key must not include control characters or whitespace.
            if ($code <= 32 || $code === 127) {
                throw KeyIsInvalid::illegalCharacter($value, $value[$i]);
            }
        }

        $this->value = $value;
    }

    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
