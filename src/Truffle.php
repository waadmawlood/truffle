<?php

namespace Waad\Truffle;

use Waad\Truffle\Concerns\CacheRecords;
use Waad\Truffle\Concerns\ConnectionAndInstance;
use Waad\Truffle\Concerns\MigrationProcess;
use Waad\Truffle\Concerns\RunTimeConnectionConfig;
use Waad\Truffle\Concerns\SetterAndGetter;

trait Truffle
{
    use CacheRecords;
    use ConnectionAndInstance;
    use MigrationProcess;
    use RunTimeConnectionConfig;
    use SetterAndGetter;

    protected static $truffleConnection;

    public static function bootTruffle()
    {
        app('db')->extend(static::class, fn () => static::resolveConnection());

        if (method_exists(static::class, 'whenBooted')) {
            static::whenBooted(function () {
                $instance = new static();
                $instance->migrate();
                $instance->migrateToDefaultConnection();
            });
        } else {
            $instance = new static();
            $instance->migrate();
            $instance->migrateToDefaultConnection();
        }
    }

    public static function deleteTruffleSqliteFile()
    {
        $file = static::getTruffleSqliteFile();
        if ($file && file_exists($file)) {
            static::clearConnections();

            return unlink($file);
        }

        return false;
    }

    public static function refreshTruffleSqliteFile()
    {
        static::deleteTruffleSqliteFile();
        static::clearConnections();

        $instance = new static();
        $instance->migrate();
    }
}
