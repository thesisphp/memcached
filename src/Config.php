<?php

declare(strict_types=1);

namespace Typhoon\Memcached;

use Typhoon\Memcached\Exception\InvalidConfiguration;
use Typhoon\Memcached\Internal\Protocol\ProtocolType;

/**
 * @api
 */
final class Config
{
    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_PORT = 11211;

    private function __construct(
        public readonly ProtocolType $protocolType,
        public readonly string $host = self::DEFAULT_HOST,
        public readonly int $port = self::DEFAULT_PORT,
        public readonly ?string $user = null,
        public readonly ?string $password = null,
    ) {}

    /**
     * @throws InvalidConfiguration
     */
    public static function fromString(string $dsn): self
    {
        if ($dsn === '') {
            throw InvalidConfiguration::emptyDSN();
        }

        $options = parse_url($dsn);

        if ($options === false) {
            throw InvalidConfiguration::incorrectDSN($dsn);
        }

        parse_str($options['query'] ?? '', $query);

        $protocolType = ProtocolType::TEXT;

        if (isset($query['proto']) && $query['proto'] !== '') {
            $protocolType = ProtocolType::tryFrom((string) $query['proto']) ?: throw InvalidConfiguration::incorrectProtocolType((string) $query['proto']);
        }

        return new self(
            $protocolType,
            $options['host'] ?? self::DEFAULT_HOST,
            $options['port'] ?? self::DEFAULT_PORT,
            $options['user'] ?? null,
            $options['pass'] ?? null,
        );
    }

    public static function default(): self
    {
        return new self(ProtocolType::TEXT);
    }

    public function uri(): string
    {
        return \sprintf('%s:%d', $this->host, $this->port);
    }
}
