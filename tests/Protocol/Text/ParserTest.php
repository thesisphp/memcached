<?php

declare(strict_types=1);

namespace Thesis\Memcached\Protocol\Text;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Thesis\Memcached\Exception\KeyAlreadyExists;
use Thesis\Memcached\Exception\KeyNotStored;
use Thesis\Memcached\Exception\MemcachedClientError;
use Thesis\Memcached\Exception\MemcachedServerError;
use Thesis\Memcached\Internal\Protocol\Text\Parser;
use Thesis\Memcached\Internal\Protocol\Text\Response;
use Thesis\Memcached\Item;

#[CoversClass(Parser::class)]
final class ParserTest extends TestCase
{
    /**
     * @return iterable<array-key, array{list<non-empty-string>, list<Response>}>
     */
    public static function fixtures(): iterable
    {
        yield 'store parsed' => [
            ["STORED\r\n"],
            [Response::ok()],
        ];

        yield 'ok parsed' => [
            ["OK\r\n"],
            [Response::ok()],
        ];

        yield 'touched parsed' => [
            ["TOUCHED\r\n"],
            [Response::ok()],
        ];

        yield 'deleted parsed' => [
            ["DELETED\r\n"],
            [Response::ok()],
        ];

        yield 'version parsed' => [
            ["VERSION 1.21\r\n"],
            [Response::ok('1.21')],
        ];

        yield 'incr/decr parsed' => [
            ["2\r\n"],
            [Response::ok(2)],
        ];

        yield 'item parsed in stream' => [
            [
                "VALUE x 0 1\r\n",
                "y\r\n",
                "END\r\n",
            ],
            [Response::ok(['x' => new Item('y')])],
        ];

        yield 'item parsed in line' => [
            ["VALUE x 0 1\r\ny\r\nEND\r\n"],
            [Response::ok(['x' => new Item('y')])],
        ];

        yield 'item with cas parsed in line' => [
            ["VALUE x 0 1 23\r\ny\r\nEND\r\n"],
            [Response::ok(['x' => new Item('y', casId: 23)])],
        ];

        yield 'key exists parsed' => [
            ["EXISTS\r\n"],
            [Response::error(new KeyAlreadyExists())],
        ];

        yield 'not stored parsed' => [
            ["NOT_STORED\r\n"],
            [Response::error(new KeyNotStored())],
        ];

        yield 'client error parsed' => [
            ["CLIENT_ERROR invalid arguments\r\n"],
            [Response::error(new MemcachedClientError('invalid arguments'))],
        ];

        yield 'server error parsed' => [
            ["SERVER_ERROR internal error\r\n"],
            [Response::error(new MemcachedServerError('internal error'))],
        ];

        yield 'error parsed' => [
            ["ERROR\r\n"],
            [Response::error(new MemcachedClientError('A nonexistent command was called.'))],
        ];
    }

    /**
     * @param list<non-empty-string> $pushes
     * @param list<Response> $responses
     */
    #[DataProvider('fixtures')]
    public function testParsed(array $pushes, array $responses): void
    {
        $parsedResponses = [];
        $parser = new Parser(static function (Response $response) use (&$parsedResponses): void {
            $parsedResponses[] = $response;
        });

        foreach ($pushes as $push) {
            $parser->push($push);
        }

        self::assertEquals($parsedResponses, $responses);
    }
}
