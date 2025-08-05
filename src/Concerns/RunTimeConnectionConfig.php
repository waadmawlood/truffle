<?php

namespace Waad\Truffle\Concerns;

trait RunTimeConnectionConfig
{
    public static function getRuntimeConnectionConfig()
    {
        return [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => static::getPrefixDatabaseName(),
            'foreign_key_constraints' => static::getForeignKeyConstraints(),
        ];
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
