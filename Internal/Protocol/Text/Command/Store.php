<?php

declare(strict_types=1);

namespace Typhoon\Memcached\Internal\Protocol\Text\Command;

use Typhoon\Memcached\Internal\Protocol\Text\Command;
use Typhoon\Memcached\Item;
use Typhoon\Memcached\Key;

/**
 * @internal
 * @psalm-internal Typhoon\Memcached
 * @psalm-type CommandName = 'set'|'add'|'replace'|'append'|'prepend'
 * @template-implements Command<void>
 */
final class Store implements Command
{
    /**
     * @psalm-param CommandName $commandName
     */
    private function __construct(
        private readonly string $commandName,
        private readonly Key $key,
        private readonly Item $item,
    ) {}

    public static function set(Key $key, Item $item): self
    {
        return new self('set', $key, $item);
    }

    public static function add(Key $key, Item $item): self
    {
        return new self('add', $key, $item);
    }

    public static function replace(Key $key, Item $item): self
    {
        return new self('replace', $key, $item);
    }

    public static function append(Key $key, Item $item): self
    {
        return new self('append', $key, $item);
    }

    public static function prepend(Key $key, Item $item): self
    {
        return new self('prepend', $key, $item);
    }

    public function asRequest(): string
    {
        return \sprintf(
            "%s %s %d %d %d\r\n%s\r\n",
            $this->commandName,
            $this->key,
            $this->item->flags,
            $this->item->expiration?->value ?? 0,
            \strlen($this->item->value),
            $this->item->value,
        );
    }
}
