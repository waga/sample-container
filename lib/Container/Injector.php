<?php

namespace App\Container;

use Exception;

class Injector
{
    const ENTITY_TYPE_BASIC = 'basic';
    const ENTITY_TYPE_CLASS = 'class';
    const ENTITY_TYPE_OBJECT = 'object';
    const ENTITY_TYPE_CALLBACK = 'callback';

    private $definitions = array();
    private $entityDefinitions = array();
    private $shared = array();
    private $options = array();

    public static function create(array $entities = array())
    {
        $injector = new self();
        return self::staticSetEntities($injector, $entities);
    }

    public function set($id, $entity = null, array $options = array())
    {
        $options = array_merge(array(
            'shared' => true,
            'type' => self::ENTITY_TYPE_CLASS
        ), $options);
        $this->definitions[$id] = $entity;
        $this->options[$id] = $options;
        return $this;
    }

    public function shared($id, $entity, array $options = array())
    {
        $this->set($id, $entity, array_merge($options, array('shared' => true)));
        return $this;
    }

    public function setEntities(array $entities)
    {
        foreach ($entities as $entity)
        {
            call_user_func_array(array($this, 'set'), $entity);
        }
        return $this;
    }

    public static function staticSetEntities(Injector $injector, array $entities)
    {
        foreach ($entities as $entity)
        {
            call_user_func_array(array($injector, 'set'), $entity);
        }
        return $injector;
    }

    public function has($id)
    {
        return isset($this->definitions[$id]) || array_key_exists($id, $this->definitions);
    }

    public function hasEntity($entity)
    {
        return in_array($entity, $this->definitions);
    }

    public function searchByEntity($entity)
    {
        return array_search($entity, $this->definitions);
    }

    public function get($id)
    {
        if (!isset($this->definitions[$id]) || !isset($this->options[$id]))
        {
            throw new Exception('Injector entity with id="'. $id .'" not found');
        }

        $entity = $this->definitions[$id];
        $options = $this->options[$id];

        if ($options['type'] == self::ENTITY_TYPE_CLASS && is_string($entity) && class_exists($entity))
        {
            $reflector = ReflectionDefinition::createReflectionClass($entity);
            $this->entityDefinitions[$id] = $reflector;

            // no constructor
            if (!$reflector->getConstructor())
            {
                return $this->getClassInstance($id, $entity);
            }

            // constructor without params
            if (!$constructorParams = $reflector->getConstructorParameters())
            {
                return $this->getClassInstance($id, $entity);
            }

            // constructor params
            $invokeParams = array();
            foreach ($constructorParams as $constructorParamName => $constructorParamValue)
            {
                if ($this->has($constructorParamName) && $constructorParamName != $id)
                {
                    $invokeParams[] = $this->get($constructorParamName);
                }
                else if ($this->hasEntity($constructorParamValue) &&
                    ($paramValue = $this->searchByEntity($constructorParamValue)) &&
                    $paramValue != $id)
                {
                    $invokeParams[] = $this->get($paramValue);
                }
            }

            return $this->getClassInstance($id, $entity, $invokeParams);
        }
        else if ($options['type'] == self::ENTITY_TYPE_CALLBACK && is_callable($entity))
        {
            $reflector = ReflectionDefinition::createReflectionFunction($entity);
            $this->entityDefinitions[$id] = $reflector;

            // function without params
            if (!$functionParams = $reflector->getFunctionParameters())
            {
                return $this->getFunctionCall($id, $entity);
            }

            // function params
            $invokeParams = array();
            foreach ($functionParams as $functionParamName => $functionParamValue)
            {
                if ($this->has($functionParamName) && $functionParamName != $id)
                {
                    $invokeParams[] = $this->get($functionParamName);
                }
                else if ($this->hasEntity($functionParamValue) &&
                    ($paramValue = $this->searchByEntity($functionParamValue)) &&
                    $functionParamValue != $id)
                {
                    $invokeParams[] = $this->get($paramValue);
                }
            }

            return $this->getFunctionCall($id, $entity, $invokeParams);
        }
        else if ($options['type'] == self::ENTITY_TYPE_OBJECT)
        {
            return $entity;
        }
        else if ($options['type'] == self::ENTITY_TYPE_BASIC)
        {
            return $entity;
        }
        throw new Exception('Invalid entity type');
    }

    protected function getClassInstance($id, $entity, array $params = array())
    {
        $options = array();
        $constructorParams = array();
        $constructorParamKeys = array();

        if (isset($this->options[$id]))
        {
            $options = $this->options[$id];
            if (isset($options['params']))
            {
                $constructorParams = $options['params'];
                $constructorParamKeys = array_keys($constructorParams);
            }
        }

        // is shared (same instance every time)
        if (isset($options['shared']) && $options['shared'])
        {
            // create shared instance
            if (!isset($this->shared[$id]))
            {
                $entityReflectionConstructor = $this->entityDefinitions[$id]->getReflection()->getConstructor();
                if ($entityReflectionConstructor)
                {
                    foreach ($entityReflectionConstructor->getParameters() as $constructorParam)
                    {
                        $constructorParamName = $constructorParam->getName();
                        if (in_array($constructorParamName, $constructorParamKeys))
                        {
                            $params[] = $constructorParams[$constructorParamName];
                        }
                    }
                }

                // save and get shared instance
                return $this->shared[$id] = $this->entityDefinitions[$id]->getReflection()->newInstanceArgs($params);
            }

            // get shared instance
            return $this->shared[$id];
        }

        // create new not shared instance
        return $this->entityDefinitions[$id]->getReflection()->newInstanceArgs($params);
    }

    protected function getFunctionCall($id, $entity, array $params = array())
    {
        $options = array();
        $functionParams = array();
        $functionParamKeys = array();

        if (isset($this->options[$id]))
        {
            $options = $this->options[$id];
            if (isset($options['params']))
            {
                $functionParams = $options['params'];
                $functionParamKeys = array_keys($functionParams);
            }
        }

        // is shared (same function every time)
        if (isset($options['shared']) && $options['shared'])
        {
            // create shared call
            if (!isset($this->shared[$id]))
            {
                $entityReflectionFunction = $this->entityDefinitions[$id]->getReflection();
                if ($entityReflectionFunction)
                {
                    foreach ($entityReflectionFunction->getParameters() as $functionParam)
                    {
                        $functionParamName = $functionParam->getName();
                        if (in_array($functionParamName, $functionParamKeys))
                        {
                            $params[] = $functionParams[$functionParamName];
                        }
                    }
                }

                // save and get shared call
                return $this->shared[$id] = $this->entityDefinitions[$id]->getReflection()->invokeArgs($params);
            }

            // get shared call
            return $this->shared[$id];
        }

        // create new not shared call
        return $this->entityDefinitions[$id]->getReflection()->invokeArgs($params);
    }
}
