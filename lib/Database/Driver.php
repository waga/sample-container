<?php

namespace App\Database;

abstract class Driver
{
    abstract public function connect($host, $user, $pass, $database, $port = null);
    abstract public function disconnect();
    abstract public function query($query, array $bindings = array());
    abstract public function getAffectedRows();
}
