<?php

namespace Waad\Truffle\Concerns;

use Illuminate\Database\Connectors\ConnectionFactory;

trait ConnectionAndInstance
{
    public static function resolveConnection($connection = null)
    {
        $config = static::getRuntimeConnectionConfig();
        app('config')->set('database.connections.'.static::class, $config);

        return static::$truffleConnection ??= app(ConnectionFactory::class)->make($config);
    }

    protected function newRelatedInstance($class)
    {
        return tap(new $class, function ($instance) {
            if (!$instance->getConnectionName()) {
                $instance->setConnection($this->getConnectionResolver()->getDefaultConnection());
            }
        });
    }

    public static function clearConnections()
    {
        static::$truffleConnection = null;
    }

    public function getConnectionName()
    {
        return static::class;
    }
}
