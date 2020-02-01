<?php

namespace App\Container;

interface InjectorWise
{
    public function setInjector(Injector $injector);
    public function getInjector();
}
