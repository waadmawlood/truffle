<?php

namespace Waad\Truffle\Concerns;

use Illuminate\Database\Connectors\ConnectionFactory;

trait ConnectionAndInstance
{
    public static function resolveConnection($connection = null)
    {
        return static::$truffleConnection ??= app(ConnectionFactory::class)->make(static::getRuntimeConnectionConfig());
    }

    protected function newRelatedInstance($class)
    {
        $instance = new $class();

        if (! $instance->getConnectionName()) {
            $instance->setConnection($this->getConnectionResolver()->getDefaultConnection());
        }

        return $instance;
    }

    public static function clearConnections()
    {
        static::$truffleConnection = null;
    }
}
