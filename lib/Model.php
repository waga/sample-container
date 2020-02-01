<?php

namespace App;

use App\Database;
use App\Database\Util;
use App\Util\ClassName;

class Model
{
    const DEFAULT_LIMIT = 20;

    protected $database;
    protected $tableName = '';

    protected $defaultLimit = self::DEFAULT_LIMIT;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->tableName = self::classToTable(get_called_class());
    }

    public function select(array $options = array())
    {
        $options = array_merge(array(
            'fields' => array('*'),
            'limit' => $this->defaultLimit,
            'offset' => 0
        ), $options);

        if (!is_array($options['fields']))
        {
            $options['fields'] = array($options['fields']);
        }

        $fields = $this->renderFields($options['fields']);
        $limit = $this->renderLimit($options['limit'], $options['offset']);

        $query = 'SELECT '. $fields .' FROM '. $this->tableName . $limit;
        return $this->database->query($query);
    }

    public function insert(array $options = array())
    {
        $options = array_merge(array(
            'fields' => array(),
            'values' => array()
        ), $options);

        if (!is_array($options['fields']))
        {
            $options['fields'] = array($options['fields']);
        }

        if (!is_array($options['values']))
        {
            $options['values'] = array($options['values']);
        }

        $preparedQuery = Util::prepareInsertQuery($this->tableName, $options['fields'], $options['values']);
        return $this->database->query($preparedQuery['query'], $preparedQuery['bindings']);
    }

    protected function renderFields(array $fields)
    {
        $renderedFields = $fields;
        if (is_array($renderedFields))
        {
            $renderedFields = join(', ', $renderedFields);
        }
        return $renderedFields;
    }

    protected function renderLimit($limit = 0, $offset = 0)
    {
        $renderedLimit = '';
        if ($limit)
        {
            $renderedLimit .= ' LIMIT ';
            if ($offset)
            {
                $renderedLimit .= $offset .', ';
            }
            $renderedLimit .= $limit;
        }
        return $renderedLimit;
    }

    public static function classToTable($class)
    {
        return implode('', array_map(function($baseClassLetter) {
            static $index = 0;
            $index++;
            $letterAscii = ord($baseClassLetter);
            $letterIsUpperCase = $letterAscii > 64 && $letterAscii < 91;
            if ($letterIsUpperCase)
            {
                return ($index > 1 ? '_' : '') . chr($letterAscii + 32);
            }
            return $baseClassLetter;
        }, str_split(ClassName::getBase($class))));
    }
}
