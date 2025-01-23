<?php

declare(strict_types=1);

namespace Thesis\Memcached\Protocol\Text;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached\Expiration;
use Thesis\Memcached\Internal\Protocol\Text\Command;
use Thesis\Memcached\Item;
use Thesis\Memcached\Key;

#[CoversClass(Command\Cas::class)]
#[CoversClass(Command\Decrement::class)]
#[CoversClass(Command\Delete::class)]
#[CoversClass(Command\FlushAll::class)]
#[CoversClass(Command\Get::class)]
#[CoversClass(Command\Gets::class)]
#[CoversClass(Command\Increment::class)]
#[CoversClass(Command\Store::class)]
#[CoversClass(Command\Touch::class)]
#[CoversClass(Command\Version::class)]
#[CoversClass(Command\Verbosity::class)]
#[CoversClass(Command\Quit::class)]
final class CommandTest extends TestCase
{
    /**
     * @return iterable<array-key, array{Command<*>, non-empty-string}>
     */
    public static function fixtures(): iterable
    {
        yield 'cas' => [
            new Command\Cas(new Key('x'), new Item('y', Expiration::fromSeconds(10), casId: 2)),
            "cas x 0 10 1 2\r\ny\r\n",
        ];

        yield 'decrement' => [
            new Command\Decrement(new Key('x'), 2),
            "decr x 2\r\n",
        ];

        yield 'delete' => [
            new Command\Delete(new Key('x')),
            "delete x\r\n",
        ];

        yield 'flush' => [
            new Command\FlushAll(),
            "flush_all\r\n",
        ];

        yield 'get' => [
            new Command\Get(new Key('x')),
            "get x\r\n",
        ];

        yield 'gets' => [
            new Command\Gets([new Key('x'), new Key('y')]),
            "gets x y\r\n",
        ];

        yield 'increment' => [
            new Command\Increment(new Key('x'), 2),
            "incr x 2\r\n",
        ];

        yield 'set' => [
            Command\Store::set(new Key('x'), new Item('y')),
            "set x 0 0 1\r\ny\r\n",
        ];

        yield 'add' => [
            Command\Store::add(new Key('x'), new Item('y', flags: 2)),
            "add x 2 0 1\r\ny\r\n",
        ];

        yield 'replace' => [
            Command\Store::replace(new Key('x'), new Item('y', expiration: Expiration::fromSeconds(10), flags: 3)),
            "replace x 3 10 1\r\ny\r\n",
        ];

        yield 'append' => [
            Command\Store::append(new Key('x'), new Item('y', expiration: Expiration::fromSeconds(10), flags: 3)),
            "append x 3 10 1\r\ny\r\n",
        ];

        yield 'prepend' => [
            Command\Store::prepend(new Key('x'), new Item('y', expiration: Expiration::fromSeconds(10), flags: 3)),
            "prepend x 3 10 1\r\ny\r\n",
        ];

        yield 'touch' => [
            new Command\Touch(new Key('x'), Expiration::fromSeconds(5)),
            "touch x 5\r\n",
        ];

        yield 'version' => [
            new Command\Version(),
            "version\r\n",
        ];

        yield 'verbosity' => [
            new Command\Verbosity(10),
            "verbosity 10\r\n",
        ];

        yield 'quit' => [
            new Command\Quit(),
            "quit\r\n",
        ];
    }

    /**
     * @param Command<*> $command
     */
    #[DataProvider('fixtures')]
    public function testCommand(Command $command, string $request): void
    {
        self::assertSame($request, $command->asRequest());
    }
}
