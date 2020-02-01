<?php

namespace App\Database\Driver;

use App\Database\Util;
use App\Database\Driver;
use Exception;
use PDO;

class PDOMySQL extends Driver
{
    protected $_databaseHandler;
    protected $_statement;

    public function connect($host, $user, $pass, $database, $port = null)
    {
        $this->_databaseHandler = new PDO('mysql:dbname='. $database .';host='. $host, $user, $pass, array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ));
        return $this;
    }

    public function disconnect()
    {
        $this->_databaseHandler = null;
    }

    public function query($query, array $bindings = array())
    {
        $queryType = Util::getQueryType($query);
        $this->_statement = $this->_databaseHandler->prepare($query);

        if (!$this->_statement)
        {
            throw new Exception('Driver query error: '. self::$db->error);
        }

        $queryResult = $this->_statement->execute($bindings);

        if ($queryType == Util::QUERY_TYPE_SELECT ||
            $queryType == Util::QUERY_TYPE_SHOW ||
            $queryType == Util::QUERY_TYPE_DESC)
        {
            return $this->_statement->fetchAll();
        }
        else if ($queryType == Util::QUERY_TYPE_INSERT)
        {
            return $this->_databaseHandler->lastInsertId();
        }

        return $queryResult;
    }

    public function getAffectedRows()
    {
        return $this->_statement->rowCount();
    }
}
