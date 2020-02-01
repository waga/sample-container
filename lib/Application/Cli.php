<?php

namespace App\Application;

use Exception;
use App\Application;
use App\Config;
use App\Router;
use App\Autoloader;
use App\Dispatcher;

class Cli extends Application
{
    public function createRequestRoute()
    {
        global $argv;
        $requestRoute = $argv;
        array_shift($requestRoute);
        if (!$requestRoute)
        {
            $requestRoute[] = '/';
        }
        return $requestRoute;
    }
}
