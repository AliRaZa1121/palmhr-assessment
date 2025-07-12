<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheService
{
    private int $ttl;

    public function __construct(
        private CacheInterface $cache,
        int $ttl = 300
    ) {
        $this->ttl = $ttl;
    }

    /**
     * Get cached data or fetch and cache it.
     */
    public function getOrSet(string $key, callable $callback, ?int $ttl = null)
    {
        $ttl = $ttl ?? $this->ttl;
        return $this->cache->get($key, function (ItemInterface $item) use ($callback, $ttl) {
            $item->expiresAfter($ttl);
            return $callback();
        });
    }

    /**
     * Delete a cache key.
     */
    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }

    /**
     * Check if cache exists for key.
     */
    public function has(string $key): bool
    {
        return $this->cache->get($key, function (ItemInterface $item) {
            return null;
        }) !== null;
    }
}
