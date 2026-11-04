<?php

declare(strict_types=1);

namespace SilverStripe\DiscovererSearchUI\Tests\Service;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Dev\TestOnly;

/**
 * Wraps a cache and records the lifetime passed to each set() call
 */
class RecordingCache implements CacheInterface, TestOnly
{

    /**
     * @var array<int|DateInterval|null>
     */
    private array $lifetimes = [];

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    /**
     * @return array<int|DateInterval|null> The lifetime passed to each set() call, in order
     */
    public function getLifetimes(): array
    {
        return $this->lifetimes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->lifetimes[] = $ttl;

        return $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     * @param iterable<string> $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->cache->getMultiple($keys, $default);
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    /**
     * @param iterable<string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

}
