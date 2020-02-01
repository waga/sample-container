<?php

namespace App;

use Exception;

use App\Config;
use App\Container\Injector;
use App\Container\ReflectionDefinition;

class Dispatcher
{
    protected $injector;

    public function setInjector(Injector $injector)
    {
        $this->injector = $injector;
        return $this;
    }

    public function dispatch($dispatcher)
    {
        $dispatcherSize = count($dispatcher);

        if ($dispatcherSize == 1)
        {
            $this->injector->set('called_dispatcher', $dispatcher[0], array('type' => Injector::ENTITY_TYPE_CALLBACK));
            $reflector = ReflectionDefinition::createReflectionFunction($dispatcher[0]);
            $methodParams = $reflector->getFunctionParameters();
        }
        else if ($dispatcherSize == 2)
        {
            $controllerClassName = '\\'. $this->injector->get('config')['controller']['namespace'] .'\\'. $dispatcher[0];
            $this->injector->set('called_dispatcher', $controllerClassName, array('type' => Injector::ENTITY_TYPE_CLASS));
            $reflector = ReflectionDefinition::createReflectionClass($controllerClassName);
            $methodParams = $reflector->getMethodParameters($dispatcher[1]);
        }
        else
        {
            throw new Exception('Invalid dispatcher');
        }

        $invokeParams = array();

        // autowire parameters
        foreach ($methodParams as $methodParamName => $methodParamValue)
        {
            if ($this->injector->has($methodParamName))
            {
                $invokeParams[] = $this->injector->get($methodParamName);
            }
            else if ($this->injector->hasEntity($methodParamValue))
            {
                $invokeParams[] = $this->injector->get($this->injector->searchByEntity($methodParamValue));
            }
        }

        if ($dispatcherSize == 1)
        {
            $callable = $this->injector->get('called_dispatcher');
        }
        else if ($dispatcherSize == 2)
        {
            // create object
            $callable = array($this->injector->get('called_dispatcher'), $dispatcher[1]);
        }
        else
        {
            throw new Exception('Invalid dispatcher');
        }

        // call method
        return call_user_func_array($callable, $invokeParams);
    }
}
