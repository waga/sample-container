<?php

namespace App;

use App\Database\Util;
use App\Database\Driver;

class Database
{
    protected $driver;

    public function __construct(Driver $driver = null)
    {
        $this->driver = $driver;
    }

    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    public function connect($host, $user, $pass, $database, $port = null)
    {
        return $this->driver->connect($host, $user, $pass, $database, $port);
    }

    public function disconnect()
    {
        return $this->driver->disconnect();
    }

    public function query($query, array $bindings = array())
    {
        return $this->driver->query($query, $bindings);
    }

    public function getAffectedRows()
    {
        return $this->driver->getAffectedRows();
    }
}
