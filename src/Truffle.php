<?php

namespace Waad\Truffle;

use Waad\Truffle\Concerns\ConnectionAndInstance;
use Waad\Truffle\Concerns\MigrationProcess;
use Waad\Truffle\Concerns\RunTimeConnectionConfig;
use Waad\Truffle\Concerns\SetterAndGetter;

trait Truffle
{
    use ConnectionAndInstance;
    use MigrationProcess;
    use RunTimeConnectionConfig;
    use SetterAndGetter;

    protected static $truffleConnection;

    public static function bootTruffle()
    {
        $instance = new static();
        static::resolveConnection();
        $instance->migrate();
    }
}
