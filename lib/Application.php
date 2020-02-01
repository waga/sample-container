<?php

namespace App;

use ErrorException;

use App\Container\InjectorWise;
use App\Container\Injector;
use App\Http\Request;

abstract class Application implements InjectorWise
{
    protected $settings;
    protected $injector;

    public function configure(array $settings = array())
    {
        $this->settings = array_merge(array(
            'error' => array(
                'handler' => function($level, $message, $file, $line) {
                    if (error_reporting() !== 0)
                    {
                        throw new ErrorException($message, 0, $level, $file, $line);
                    }
                }
            ),
            'exception' => array(
                'handler' => function($exception) {
                    // echo print_r($exception->getMessage(), true) . PHP_EOL;
                    echo print_r($exception, true) . PHP_EOL;
                }
            )
        ), $settings);
        return $this;
    }

    public function initialize()
    {
        error_reporting(E_ALL);
        set_error_handler($this->settings['error']['handler']);
        set_exception_handler($this->settings['exception']['handler']);
        setlocale(LC_ALL, NULL);
        return $this;
    }

    public function dispatch()
    {
        $config = $this->injector->get('config');
        $dispatcher = $this->injector->get('router')->route($this->createRequestRoute());

        if (!$dispatcher)
        {
            throw new Exception('Invalid dispatcher');
        }

        if ($config['controller'])
        {
            // register controller class for autoloading
            $this->injector->get('autoloader')->register(
                $config['controller']['dir'],
                $config['controller']['namespace']);
        }

        if ($config['model'])
        {
            // register model class for autoloading
            $this->injector->get('autoloader')->register(
                $config['model']['dir'],
                $config['model']['namespace']);
        }

        return $this->injector
            ->get('dispatcher')
            ->setInjector($this->injector)
            ->dispatch($dispatcher);
    }

    public function setInjector(Injector $injector)
    {
        $this->injector = $injector;
        return $this;
    }

    public function getInjector()
    {
        return $this->injector;
    }

    abstract public function createRequestRoute();
}
