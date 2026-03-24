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
                (new static())->migrate();
            });
        } else {
            (new static())->migrate();
        }
    }
}
