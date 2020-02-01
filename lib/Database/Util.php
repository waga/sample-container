<?php

namespace App\Database;

class Util
{
    const SELECT_FIELD_STAR = '*';

    const QUERY_TYPE_UNKNOWN = 0;
    const QUERY_TYPE_SELECT = 1;
    const QUERY_TYPE_INSERT = 2;
    const QUERY_TYPE_UPDATE = 3;
    const QUERY_TYPE_DELETE = 4;
    const QUERY_TYPE_SHOW = 5;
    const QUERY_TYPE_DESC = 6;

    const QUERY_TYPE_UNKNOWN_STRING = 'unknown';
    const QUERY_TYPE_SELECT_STRING = 'select';
    const QUERY_TYPE_INSERT_STRING = 'insert';
    const QUERY_TYPE_UPDATE_STRING = 'update';
    const QUERY_TYPE_DELETE_STRING = 'delete';
    const QUERY_TYPE_SHOW_STRING = 'show';
    const QUERY_TYPE_DESC_STRING = 'desc';

    const DEFAULT_BIND_PARAM_PLACEHOLDER = '?';
    const DEFAULT_USE_PARAM_BINDING = true;

    const STRING_VALUE_NULL = 'null';

    /**
     * Backtick given input
     *
     * @static
     * @param string $input Input value
     * @return string Backticked input
     */
    public static function backtick($input)
    {
        return '`'. $input .'`';
    }

    /**
     * Quote given input
     *
     * @static
     * @param string $input              Input value
     * @param mixed  $singleButNotDouble (optional) Specify quote type
     * @return string Quoted input
     */
    public static function quote($input, $singleButNotDouble = true)
    {
        if ($singleButNotDouble) {
            return "'". $input ."'";
        }
        return '"'. $input .'"';
    }

    /**
     * Get query type
     *
     * Query type is determined based on specific string
     * stripos is used to detect the specific string.
     *
     * @static
     * @param string $query SQL query
     * @return integer Query type constant
     */
    public static function getQueryType($query)
    {
        if (false !== stripos($query, 'select'))
        {
            return self::QUERY_TYPE_SELECT;
        }
        else if (false !== stripos($query, 'insert'))
        {
            return self::QUERY_TYPE_INSERT;
        }
        else if (false !== stripos($query, 'update'))
        {
            return self::QUERY_TYPE_UPDATE;
        }
        else if (false !== stripos($query, 'delete'))
        {
            return self::QUERY_TYPE_DELETE;
        }
        else if (false !== stripos($query, 'show'))
        {
            return self::QUERY_TYPE_SHOW;
        }
        else if (false !== stripos($query, 'desc'))
        {
            return self::QUERY_TYPE_DESC;
        }
        return self::QUERY_TYPE_UNKNOWN;
    }

    /**
     * Get query type as string
     *
     * Simmilar to Util::getQueryType() but different return.
     *
     * @static
     * @see Util::getQueryType()
     * @param string $query SQL query
     * @return string Query type string constant
     */
    public static function getQueryTypeStringByQuery($query)
    {
        if (false !== stripos($query, 'select'))
        {
            return self::QUERY_TYPE_SELECT_STRING;
        }
        else if (false !== stripos($query, 'insert'))
        {
            return self::QUERY_TYPE_INSERT_STRING;
        }
        else if (false !== stripos($query, 'update'))
        {
            return self::QUERY_TYPE_UPDATE_STRING;
        }
        else if (false !== stripos($query, 'delete'))
        {
            return self::QUERY_TYPE_DELETE_STRING;
        }
        else if (false !== stripos($query, 'show'))
        {
            return self::QUERY_TYPE_SHOW_STRING;
        }
        else if (false !== stripos($query, 'desc'))
        {
            return self::QUERY_TYPE_DESC_STRING;
        }
        return self::QUERY_TYPE_UNKNOWN_STRING;
    }

    /**
     * Get query type as string by given Util query type constant
     *
     * @static
     * @param integer $queryType SQL query type
     * @return string Query type string constant
     */
    public static function getQueryTypeStringByQueryType($queryType)
    {
        if ($queryType == self::QUERY_TYPE_SELECT)
        {
            return self::QUERY_TYPE_SELECT_STRING;
        }
        else if ($queryType == self::QUERY_TYPE_INSERT)
        {
            return self::QUERY_TYPE_INSERT_STRING;
        }
        else if ($queryType == self::QUERY_TYPE_UPDATE)
        {
            return self::QUERY_TYPE_UPDATE_STRING;
        }
        else if ($queryType == self::QUERY_TYPE_DELETE)
        {
            return self::QUERY_TYPE_DELETE_STRING;
        }
        else if ($queryType == self::QUERY_TYPE_SHOW)
        {
            return self::QUERY_TYPE_SHOW_STRING;
        }
        else if ($queryType == self::QUERY_TYPE_DESC)
        {
            return self::QUERY_TYPE_DESC_STRING;
        }
        return self::QUERY_TYPE_UNKNOWN_STRING;
    }

    /**
     * Render table alias
     *
     * @static
     * @param string $tableName Name of the table
     * @param string $alias     (optional) Alias of the table
     * @return string Rendered table with alias if alias is supplied, otherwise
     *   return table only.
     */
    public static function renderTableAlias($tableName, $alias = '')
    {
        return $alias ? $tableName .' AS '. $alias : $tableName;
    }

    /**
     * Render field alias
     *
     * @static
     * @param string $fieldName Name of the field
     * @param string $alias     (optional) Alias of the field
     * @return string Rendered field with alias if alias is supplied, otherwise
     *   return field only.
     */
    public static function renderFieldAlias($fieldName, $alias = '')
    {
        return $alias ? $alias .'.'. $fieldName : $fieldName;
    }

    /**
     * Create table alias
     *
     * This method work properly only with tables that include underscore
     *   example: Util::createTableAlias('table_name') generate alias 'tn'
     *
     * @static
     * @param string $tableName Name of the table
     * @return string Rendered table alias by table name, taking consideration
     *   table name.
     */
    public static function createTableAlias($tableName)
    {
        $tokens = explode('_', $tableName);
        $alias = '';
        foreach ($tokens as $token)
        {
            $alias .= $token[0];
        }
        return $alias;
    }

    public static function prepareInsertBatchQuery($table, array $fields, array $data)
    {
        $placeholders = array();
        $bindings = array();
        $placeholder = '('. join(', ', array_fill(0, count($fields), '?')) .')';

        foreach ($data as $row)
        {
            $placeholders[] = $placeholder;
            foreach ($fields as $field)
            {
                $bindings[] = $row[$field];
            }
        }

        return array(
            'query' => 'INSERT INTO `'. $table .'` (`'. join('`, `', $fields) .'`) VALUES '. join(', ', $placeholders),
            'bindings' => $bindings
        );
    }

    public static function prepareInsertQuery($table, array $fields, array $data)
    {
        $fieldsCount = count($fields);
        $fields = '(`'. join('`, `', $fields) .'`)';
        $values = '('. join(', ', array_fill(0, $fieldsCount, '?')) .')';
        return array(
            'query' => 'INSERT INTO `'. $table .'` '. $fields .' VALUES '. $values,
            'bindings' => array_values($data)
        );
    }
}
