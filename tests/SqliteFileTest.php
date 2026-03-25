<?php

namespace Waad\Truffle\Tests;

use Waad\Truffle\Tests\Models\FilePeople;

class SqliteFileTest extends TestCase
{
    protected string $sqliteFilePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqliteFilePath = sys_get_temp_dir().'/truffle_test_'.uniqid().'.sqlite';
        FilePeople::setTruffleSqliteFile($this->sqliteFilePath);
        FilePeople::clearConnections();
    }

    protected function tearDown(): void
    {
        FilePeople::clearConnections();
        if (file_exists($this->sqliteFilePath)) {
            unlink($this->sqliteFilePath);
        }
        FilePeople::setTruffleSqliteFile(null);
        parent::tearDown();
    }

    // ── Configuration ──────────────────────────────────────────

    public function test_model_reports_sqlite_file_mode(): void
    {
        $this->assertTrue(FilePeople::isTruffleSqliteFile());
        $this->assertEquals($this->sqliteFilePath, FilePeople::getTruffleSqliteFile());
    }

    public function test_connection_config_uses_file_path(): void
    {
        $config = FilePeople::getRuntimeConnectionConfig();
        $this->assertEquals($this->sqliteFilePath, $config['database']);
        $this->assertEquals('sqlite', $config['driver']);
    }

    public function test_in_memory_model_reports_no_sqlite_file(): void
    {
        FilePeople::setTruffleSqliteFile(null);
        $this->assertFalse(FilePeople::isTruffleSqliteFile());
        $this->assertNull(FilePeople::getTruffleSqliteFile());

        $config = FilePeople::getRuntimeConnectionConfig();
        $this->assertEquals(':memory:', $config['database']);
    }

    // ── File creation ──────────────────────────────────────────

    public function test_sqlite_file_is_created_on_boot(): void
    {
        $this->assertFileDoesNotExist($this->sqliteFilePath);

        FilePeople::all();

        $this->assertFileExists($this->sqliteFilePath);
    }

    public function test_sqlite_file_directory_is_created_automatically(): void
    {
        $nestedPath = sys_get_temp_dir().'/truffle_nested_'.uniqid().'/subdir/test.sqlite';
        FilePeople::setTruffleSqliteFile($nestedPath);
        FilePeople::clearConnections();

        FilePeople::all();

        $this->assertFileExists($nestedPath);

        unlink($nestedPath);
        rmdir(dirname($nestedPath));
        rmdir(dirname(dirname($nestedPath)));
    }

    // ── Data querying ──────────────────────────────────────────

    public function test_data_is_queryable_from_file(): void
    {
        $data = FilePeople::all();
        $this->assertCount(3, $data);
    }

    public function test_can_find_record_by_id(): void
    {
        $person = FilePeople::find(1);
        $this->assertNotNull($person);
        $this->assertEquals('Alice', $person->name);
        $this->assertEquals('alice@example.com', $person->email);
        $this->assertEquals('admin', $person->role);
    }

    public function test_can_filter_records(): void
    {
        $admins = FilePeople::where('role', 'admin')->get();
        $this->assertCount(1, $admins);
        $this->assertEquals('Alice', $admins->first()->name);
    }

    public function test_can_aggregate_records(): void
    {
        $count = FilePeople::count();
        $this->assertEquals(3, $count);
    }

    // ── Persistence ────────────────────────────────────────────

    public function test_data_persists_after_clearing_connection(): void
    {
        FilePeople::all();
        $this->assertFileExists($this->sqliteFilePath);

        FilePeople::clearConnections();

        $data = FilePeople::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Alice', $data->first()->name);
    }

    public function test_skip_migration_when_table_already_exists(): void
    {
        FilePeople::all();
        $this->assertCount(3, FilePeople::all());

        FilePeople::clearConnections();

        $data = FilePeople::all();
        $this->assertCount(3, $data, 'Should not duplicate records on re-connect');
    }

    // ── File management ────────────────────────────────────────

    public function test_delete_sqlite_file(): void
    {
        FilePeople::all();
        $this->assertFileExists($this->sqliteFilePath);

        $result = FilePeople::deleteTruffleSqliteFile();
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->sqliteFilePath);
    }

    public function test_delete_sqlite_file_returns_false_when_no_file(): void
    {
        $this->assertFileDoesNotExist($this->sqliteFilePath);
        $result = FilePeople::deleteTruffleSqliteFile();
        $this->assertFalse($result);
    }

    public function test_refresh_sqlite_file_rebuilds_data(): void
    {
        FilePeople::all();
        $this->assertFileExists($this->sqliteFilePath);

        FilePeople::refreshTruffleSqliteFile();

        $this->assertFileExists($this->sqliteFilePath);
        $data = FilePeople::all();
        $this->assertCount(3, $data);
        $this->assertEquals('Alice', $data->first()->name);
    }
}
