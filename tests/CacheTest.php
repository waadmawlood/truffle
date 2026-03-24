<?php

namespace Waad\Truffle\Tests;

use Illuminate\Support\Facades\Cache;
use Waad\Truffle\Tests\Models\CachedPeople;
use Waad\Truffle\Tests\Models\CachedWithDriverPeople;
use Waad\Truffle\Tests\Models\CacheForeverPeople;
use Waad\Truffle\Tests\Models\Country;
use Waad\Truffle\Tests\Models\People;

class CacheTest extends TestCase
{
    protected function tearDown(): void
    {
        CachedPeople::clearTruffleCache();
        CachedPeople::clearConnections();
        CachedWithDriverPeople::clearTruffleCache();
        CachedWithDriverPeople::clearConnections();
        CacheForeverPeople::clearTruffleCache();
        CacheForeverPeople::clearConnections();
        People::clearConnections();
        Country::clearConnections();
        parent::tearDown();
    }

    // ── Cache disabled by default ───────────────────────────────

    public function test_cache_is_disabled_by_default(): void
    {
        $model = new People();
        $this->assertFalse($model->isTruffleCacheEnabled());
    }

    public function test_uncached_model_does_not_write_to_cache(): void
    {
        $model = new People();
        $key = $model->getTruffleCacheKey();

        People::all();

        $this->assertFalse(Cache::has($key));
    }

    public function test_uncached_model_still_works_normally(): void
    {
        $data = People::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Waad Mawlood', $data->first()->name);
    }

    // ── Cache enabled model ─────────────────────────────────────

    public function test_cache_can_be_enabled_on_model(): void
    {
        $model = new CachedPeople();
        $this->assertTrue($model->isTruffleCacheEnabled());
    }

    public function test_cached_model_stores_records_in_cache(): void
    {
        $model = new CachedPeople();
        $key = $model->getTruffleCacheKey();

        CachedPeople::all();

        $this->assertTrue(Cache::has($key));
    }

    public function test_cached_model_data_is_queryable(): void
    {
        $data = CachedPeople::all();
        $this->assertCount(3, $data);

        $alice = CachedPeople::where('name', 'Alice')->first();
        $this->assertNotNull($alice);
        $this->assertEquals('alice@example.com', $alice->email);
        $this->assertEquals('admin', $alice->role);
    }

    public function test_cached_records_match_original_records(): void
    {
        $model = new CachedPeople();
        $key = $model->getTruffleCacheKey();

        CachedPeople::all();

        $cached = Cache::get($key);
        $this->assertIsArray($cached);
        $this->assertCount(3, $cached);
        $this->assertEquals('Alice', $cached[0]['name']);
        $this->assertEquals('Bob', $cached[1]['name']);
        $this->assertEquals('Charlie', $cached[2]['name']);
    }

    // ── TTL ─────────────────────────────────────────────────────

    public function test_cache_ttl_is_configurable(): void
    {
        $model = new CachedPeople();
        $this->assertEquals(300, $model->getTruffleCacheTtl());
    }

    public function test_cache_ttl_defaults_to_null_for_forever(): void
    {
        $model = new CacheForeverPeople();
        $this->assertNull($model->getTruffleCacheTtl());
    }

    public function test_cache_forever_model_stores_records(): void
    {
        $model = new CacheForeverPeople();
        $key = $model->getTruffleCacheKey();

        CacheForeverPeople::all();

        $this->assertTrue(Cache::has($key));
        $cached = Cache::get($key);
        $this->assertCount(2, $cached);
    }

    // ── Cache driver ────────────────────────────────────────────

    public function test_cache_driver_is_configurable(): void
    {
        $model = new CachedWithDriverPeople();
        $this->assertEquals('array', $model->getTruffleCacheDriver());
    }

    public function test_cache_driver_defaults_to_null(): void
    {
        $model = new CachedPeople();
        $this->assertNull($model->getTruffleCacheDriver());
    }

    public function test_model_with_custom_driver_stores_data(): void
    {
        $data = CachedWithDriverPeople::all();
        $this->assertCount(2, $data);
        $this->assertEquals('Dave', $data->first()->name);
    }

    // ── Cache prefix ────────────────────────────────────────────

    public function test_cache_prefix_defaults_to_truffle(): void
    {
        $model = new CachedPeople();
        $this->assertEquals('truffle_', $model->getTruffleCachePrefix());
    }

    public function test_cache_prefix_is_customizable(): void
    {
        $model = new CachedWithDriverPeople();
        $this->assertEquals('custom_prefix_', $model->getTruffleCachePrefix());
    }

    public function test_cache_key_contains_prefix(): void
    {
        $model = new CachedPeople();
        $this->assertStringStartsWith('truffle_', $model->getTruffleCacheKey());

        $model2 = new CachedWithDriverPeople();
        $this->assertStringStartsWith('custom_prefix_', $model2->getTruffleCacheKey());
    }

    public function test_cache_key_is_unique_per_model(): void
    {
        $key1 = (new CachedPeople())->getTruffleCacheKey();
        $key2 = (new CachedWithDriverPeople())->getTruffleCacheKey();
        $key3 = (new CacheForeverPeople())->getTruffleCacheKey();

        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
        $this->assertNotEquals($key2, $key3);
    }

    // ── Cache clearing ──────────────────────────────────────────

    public function test_clear_truffle_cache_removes_cached_records(): void
    {
        $model = new CachedPeople();
        $key = $model->getTruffleCacheKey();

        CachedPeople::all();
        $this->assertTrue(Cache::has($key));

        CachedPeople::clearTruffleCache();
        $this->assertFalse(Cache::has($key));
    }

    public function test_clear_cache_does_not_affect_other_models(): void
    {
        $key1 = (new CachedPeople())->getTruffleCacheKey();
        $key2 = (new CacheForeverPeople())->getTruffleCacheKey();

        CachedPeople::all();
        CacheForeverPeople::all();

        $this->assertTrue(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));

        CachedPeople::clearTruffleCache();

        $this->assertFalse(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));
    }

    // ── Refresh cache ───────────────────────────────────────────

    public function test_refresh_truffle_cache_returns_fresh_records(): void
    {
        CachedPeople::all();

        $refreshed = CachedPeople::refreshTruffleCache();
        $this->assertIsArray($refreshed);
        $this->assertCount(3, $refreshed);
    }

    // ── Uncached model (Country) alongside cached model ─────────

    public function test_uncached_country_model_is_not_affected_by_caching(): void
    {
        $country = new Country();
        $this->assertFalse($country->isTruffleCacheEnabled());

        $uk = Country::find('UK');
        $this->assertNotNull($uk);
        $this->assertEquals('United Kingdom', $uk->name);

        $key = $country->getTruffleCacheKey();
        $this->assertFalse(Cache::has($key));
    }

    public function test_cached_and_uncached_models_coexist(): void
    {
        $cachedData = CachedPeople::all();
        $this->assertCount(3, $cachedData);

        $uncachedData = Country::all();
        $this->assertCount(3, $uncachedData);

        $cachedKey = (new CachedPeople())->getTruffleCacheKey();
        $uncachedKey = (new Country())->getTruffleCacheKey();

        $this->assertTrue(Cache::has($cachedKey));
        $this->assertFalse(Cache::has($uncachedKey));
    }

    // ── getCachedRecords() behavior ─────────────────────────────

    public function test_get_cached_records_returns_array(): void
    {
        $model = new CachedPeople();
        $records = $model->getCachedRecords();

        $this->assertIsArray($records);
        $this->assertCount(3, $records);
    }

    public function test_get_cached_records_on_uncached_model_returns_raw(): void
    {
        $model = new People();
        $records = $model->getCachedRecords();

        $this->assertIsArray($records);
        $this->assertCount(3, $records);
        $this->assertEquals('Waad Mawlood', $records[0]['name']);
    }
}
