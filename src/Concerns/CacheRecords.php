<?php

namespace Waad\Truffle\Concerns;

use Illuminate\Support\Facades\Cache;

trait CacheRecords
{
    public function isTruffleCacheEnabled()
    {
        return isset($this->truffleCache) ? (bool) $this->truffleCache : false;
    }

    public function getTruffleCacheDriver()
    {
        return isset($this->truffleCacheDriver) ? $this->truffleCacheDriver : null;
    }

    public function getTruffleCacheTtl()
    {
        return isset($this->truffleCacheTtl) ? (int) $this->truffleCacheTtl : null;
    }

    public function getTruffleCachePrefix()
    {
        return isset($this->truffleCachePrefix) ? $this->truffleCachePrefix : 'truffle_';
    }

    public function getTruffleCacheKey()
    {
        return $this->getTruffleCachePrefix().md5(static::class);
    }

    protected function getTruffleCacheStore()
    {
        $driver = $this->getTruffleCacheDriver();

        return $driver ? Cache::store($driver) : Cache::store();
    }

    public function getCachedRecords()
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

    public static function clearTruffleCache()
    {
        $instance = new static();

        return $instance->getTruffleCacheStore()->forget($instance->getTruffleCacheKey());
    }

    public static function refreshTruffleCache()
    {
        static::clearTruffleCache();

        $instance = new static();

        return $instance->getCachedRecords();
    }
}
