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
        $records = $this->getRecords();
        ! empty($records) ?
            $this->createTable(reset($records)) :
            $this->createEmptyTable();

        $chunkRecordsInt = $this->getInsertChunkRecords();
        $chunkRecords = ! empty($records) ? array_chunk($records, $chunkRecordsInt) : [];
        foreach ($chunkRecords as $records) {
            // Convert arrays to JSON for database insertion
            $processedRecords = array_map(function ($record) {
                foreach ($record as $key => $value) {
                    if (is_array($value)) {
                        $record[$key] = json_encode($value);
                    }
                }

                return $record;
            }, $records);

            static::insert($processedRecords);
        }
    }

    public function createTable($firstRecord)
    {
        $this->createTableSafely($this->getTable(), function ($table) use ($firstRecord) {
            $schema = $this->getSchema();
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

            $firstRecordKeys = array_keys($firstRecord);
            if ($this->usesTimestamps() && (! in_array('updated_at', $firstRecordKeys) || ! in_array('created_at', $firstRecordKeys))) {
                $table->timestamps();
            }

            $this->thenMigration($table);
        });
    }

    public function createEmptyTable()
    {
        $this->createTableSafely($this->getTable(), function ($table) {
            $schema = $this->getSchema();
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

            $schemaKeys = array_keys($schema);
            if ($this->usesTimestamps() && (! in_array('updated_at', $schemaKeys) || ! in_array('created_at', $schemaKeys))) {
                $table->timestamps();
            }
        });
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
