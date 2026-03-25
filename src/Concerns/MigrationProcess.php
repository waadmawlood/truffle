<?php

namespace Waad\Truffle\Concerns;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Waad\Truffle\Enums\DataType;

trait MigrationProcess
{
    public function migrate()
    {
        if ($this->shouldSkipMigration()) {
            return;
        }

        $records = $this->getCachedRecords();
        ! empty($records) ?
            $this->createTable(reset($records)) :
            $this->createEmptyTable();

        $chunkRecordsInt = $this->getInsertChunkRecords();
        $chunkRecords = ! empty($records) ? array_chunk($records, $chunkRecordsInt) : [];
        foreach ($chunkRecords as $records) {
            $processedRecords = $this->processRecordsForInsert($records);

            static::insert($processedRecords);
        }
    }

    protected function shouldSkipMigration()
    {
        if (! static::isTruffleSqliteFile()) {
            return false;
        }

        $file = static::getTruffleSqliteFile();
        if (! $file || ! file_exists($file) || filesize($file) === 0) {
            return false;
        }

        $connection = static::resolveConnection();
        if (! $connection) {
            return false;
        }

        return $connection->getSchemaBuilder()->hasTable($this->getTable());
    }

    public function migrateToDefaultConnection()
    {
        $defaultConnectionName = config('database.default');

        if ($defaultConnectionName === $this->getConnectionName()) {
            return;
        }

        $connection = app('db')->connection($defaultConnectionName);
        $tableName = $this->getTable();

        if ($connection->getSchemaBuilder()->hasTable($tableName)) {
            return;
        }

        $records = $this->getCachedRecords();
        $firstRecord = ! empty($records) ? reset($records) : null;

        try {
            $connection->getSchemaBuilder()->create($tableName, function (Blueprint $table) use ($firstRecord) {
                $table->temporary();
                $this->buildColumnDefinitions($table, $firstRecord);
            });
        } catch (QueryException $e) {
            if (! Str::contains($e->getMessage(), ['already exists'])) {
                throw $e;
            }

            return;
        }

        if (! empty($records)) {
            $chunkInt = $this->getInsertChunkRecords();
            foreach (array_chunk($records, $chunkInt) as $chunk) {
                $connection->table($tableName)->insert(
                    $this->processRecordsForInsert($chunk)
                );
            }
        }
    }

    public function createTable($firstRecord)
    {
        $this->createTableSafely($this->getTable(), function ($table) use ($firstRecord) {
            $this->buildColumnDefinitions($table, $firstRecord);
            $this->thenMigration($table);
        });
    }

    public function createEmptyTable()
    {
        $this->createTableSafely($this->getTable(), function ($table) {
            $this->buildColumnDefinitions($table);
        });
    }

    protected function buildColumnDefinitions(Blueprint $table, $firstRecord = null)
    {
        $schema = $this->getSchema();

        if ($firstRecord !== null) {
            foreach ($firstRecord as $column => $value) {
                if (is_int($value)) {
                    $type = DataType::Integer;
                } elseif (is_numeric($value)) {
                    $type = DataType::Float;
                } elseif (is_string($value)) {
                    $type = DataType::String;
                } elseif (is_object($value) && $value instanceof \DateTime) {
                    $type = 'dateTime';
                } else {
                    $type = DataType::String;
                }

                $type = isset($schema[$column]) ? $schema[$column] : $type;
                if ($column === $this->primaryKey && in_array($type, [DataType::Id, DataType::UnsignedBigInteger, DataType::BigInteger])) {
                    $table->increments($this->primaryKey);

                    continue;
                }
                if ($column === $this->primaryKey && in_array($type, [DataType::Uuid, DataType::Ulid, DataType::String])) {
                    $table->string($this->primaryKey)->primary();

                    continue;
                }

                $table->{$type}($column)->nullable();
            }

            $keys = array_keys($firstRecord);
        } else {
            foreach ($schema as $name => $type) {
                if ($name === $this->primaryKey && in_array($type, [DataType::Id, DataType::UnsignedBigInteger, DataType::BigInteger])) {
                    $table->increments($this->primaryKey);

                    continue;
                }
                if ($name === $this->primaryKey && in_array($type, [DataType::Uuid, DataType::Ulid, DataType::String])) {
                    $table->string($this->primaryKey)->primary();

                    continue;
                }

                $table->{$type}($name)->nullable();
            }

            $keys = array_keys($schema);
        }

        if ($this->usesTimestamps() && (! in_array('updated_at', $keys) || ! in_array('created_at', $keys))) {
            $table->timestamps();
        }
    }

    protected function processRecordsForInsert(array $records)
    {
        return array_map(function ($record) {
            foreach ($record as $key => $value) {
                if (is_array($value)) {
                    $record[$key] = json_encode($value);
                }
            }

            return $record;
        }, $records);
    }

    protected function createTableSafely(string $tableName, Closure $callback)
    {
        $connection = static::resolveConnection();
        if (! $connection) {
            throw new \RuntimeException('Truffle connection is not set. Did you forget to call bootTruffle()?');
        }

        $schemaBuilder = $connection->getSchemaBuilder();

        try {
            $schemaBuilder->create($tableName, $callback);
        } catch (QueryException $e) {
            $msg = $e->getMessage();
            if (
                Str::contains($msg, [
                    'already exists (SQL: create table',
                    sprintf('table "%s" already exists', $tableName),
                ])
            ) {
                return;
            }
            throw $e;
        }
    }

    protected function thenMigration(Blueprint $table)
    {
        // Hook for child classes
    }
}
