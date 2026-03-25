<?php

namespace Waad\Truffle\Concerns;

trait RunTimeConnectionConfig
{
    public static function getRuntimeConnectionConfig()
    {
        $database = ':memory:';

        if (static::isTruffleSqliteFile()) {
            $database = static::getTruffleSqliteFile();
            $directory = dirname($database);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            if (! file_exists($database)) {
                touch($database);
            }
        }

        return [
            'driver' => 'sqlite',
            'database' => $database,
            'prefix' => static::getPrefixDatabaseName(),
            'foreign_key_constraints' => static::getForeignKeyConstraints(),
        ];
    }

    public static function getTruffleSqliteFile()
    {
        return isset(static::$truffleSqliteFile) ? static::$truffleSqliteFile : null;
    }

    public static function isTruffleSqliteFile()
    {
        return static::getTruffleSqliteFile() !== null;
    }

    public static function getPrefixDatabaseName()
    {
        return isset(static::$prefixDatabaseName) ? static::$prefixDatabaseName : '';
    }

    public static function getForeignKeyConstraints()
    {
        return isset(static::$foreignKeyConstraints) ? static::$foreignKeyConstraints : true;
    }
}
