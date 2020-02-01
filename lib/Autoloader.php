<?php

namespace App;

class Autoloader
{
    protected $loaders = array();

    public function __construct()
    {
        $this->register(dirname(__FILE__));
    }

    public static function create()
    {
        return new self();
    }

    public function register($path, $namespace = __NAMESPACE__)
    {
        $this->loaders[$namespace] = $path;
        spl_autoload_register(array($this, 'loadClass'));
        return $this;
    }

    public function loadClass($class)
    {
        $className = ltrim($class, '\\');
        if ($lastNamespacePosition = strpos($className, '\\'))
        {
            $namespace = substr($className, 0, $lastNamespacePosition);
            $className = substr($className, $lastNamespacePosition + 1);
            include $this->loaders[$namespace] .'/'. str_replace('\\', '/', $className) .'.php';
        }
        return $this;
    }
}
