<?php

namespace Waad\Truffle\Concerns;

use Illuminate\Support\Facades\Cache;

trait CacheRecords
{
    public function isTruffleCacheEnabled(): bool
    {
        return isset($this->truffleCache) ? (bool) $this->truffleCache : false;
    }

    public function getTruffleCacheDriver(): ?string
    {
        return isset($this->truffleCacheDriver) ? $this->truffleCacheDriver : null;
    }

    public function getTruffleCacheTtl(): ?int
    {
        return isset($this->truffleCacheTtl) ? (int) $this->truffleCacheTtl : null;
    }

    public function getTruffleCachePrefix(): string
    {
        return isset($this->truffleCachePrefix) ? $this->truffleCachePrefix : 'truffle_';
    }

    public function getTruffleCacheKey(): string
    {
        return $this->getTruffleCachePrefix().md5(static::class);
    }

    protected function getTruffleCacheStore()
    {
        $driver = $this->getTruffleCacheDriver();

        return $driver ? Cache::store($driver) : Cache::store();
    }

    public function getCachedRecords(): array
    {
        if (! $this->isTruffleCacheEnabled()) {
            return $this->getRecords();
        }

        $store = $this->getTruffleCacheStore();
        $key = $this->getTruffleCacheKey();
        $ttl = $this->getTruffleCacheTtl();

        if ($ttl !== null) {
            return $store->remember($key, $ttl, fn () => $this->getRecords());
        }

        return $store->rememberForever($key, fn () => $this->getRecords());
    }

    public static function clearTruffleCache(): bool
    {
        $instance = new static();

        return $instance->getTruffleCacheStore()->forget($instance->getTruffleCacheKey());
    }

    public static function refreshTruffleCache(): array
    {
        static::clearTruffleCache();

        $instance = new static();

        return $instance->getCachedRecords();
    }
}
