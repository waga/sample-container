<?php

namespace App\Container;

use ReflectionClass;
use ReflectionFunction;

class ReflectionDefinition
{
    private $reflection;

    public function setReflection($reflection)
    {
        $this->reflection = $reflection;
        return $this;
    }

    public function getReflection()
    {
        return $this->reflection;
    }

    public function getConstructor()
    {
        return $this->reflection->getConstructor();
    }

    public function getConstructorParameters()
    {
        return $this->getMethodParameters(
            $this->reflection->getConstructor()->getName());
    }

    public function getMethodParameters($method)
    {
        $params = array();
        foreach ($this->reflection->getMethod($method)->getParameters() as $param)
        {
            $paramName = $param->getName();
            $paramClass = $param->getClass();
            if ($paramClass)
            {
                $params[$paramName] = $paramClass->getName();
            }
            else
            {
                $params[$paramName] = $paramName;
            }
        }
        return $params;
    }

    public function getFunctionParameters()
    {
        $params = array();
        foreach ($this->reflection->getParameters() as $param)
        {
            $paramName = $param->getName();
            $paramClass = $param->getClass();
            if ($paramClass)
            {
                $params[$paramName] = $paramClass->getName();
            }
            else
            {
                $params[$paramName] = $paramName;
            }
        }
        return $params;
    }

    public static function createReflectionClass($class)
    {
        return (new self())->setReflection(new ReflectionClass($class));
    }

    public static function createReflectionFunction($function)
    {
        return (new self())->setReflection(new ReflectionFunction($function));
    }
}
