<?php

namespace App;

use ArrayAccess;
use Exception;

class Config implements ArrayAccess
{
    private $config;

    public function __construct($config = null)
    {
        if (is_string($config))
        {
            $this->config = self::loadFromFile($config);
            return;
        }
        else if (is_array($config))
        {
            $this->config = $config;
            return;
        }
        throw new Exception('Unknown config type');
    }

    public function get($key, $defaultReturnValue = null)
    {
        if (isset($this->config[$key]) || array_key_exists($key, $this->config))
        {
            return $this->config[$key];
        }
        return $defaultReturnValue;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->config[] = $value;
        }
        else
        {
            $this->config[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    public static function loadFromFile($config)
    {
        if (!file_exists($config))
        {
            throw new Exception('Config file not found');
        }

        $info = pathinfo($config);

        switch ($info['extension'])
        {
            case 'php': return include $config;
            default: break;
        }

        throw new Exception('Unknown config file type');
    }
}
