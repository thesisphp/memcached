<?php

declare(strict_types=1);

namespace Thesis\Memcached\Exception;

use Thesis\Memcached\MemcachedException;

/**
 * @api
 */
final class KeyIsInvalid extends MemcachedException
{
    public static function keyIsEmpty(): self
    {
        return new self('The key is empty.');
    }

    public static function tooLong(string $key): self
    {
        return new self(\sprintf('The key "%s" is too long.', $key));
    }

    public static function illegalCharacter(string $key, string $character): self
    {
        return new self(\sprintf('The key "%s" contains illegal character "%s".', $key, $character));
    }
}
