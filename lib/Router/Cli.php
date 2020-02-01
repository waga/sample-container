<?php

namespace App\Router;

use App\Router;

class Cli extends Router
{
    public function route(array $requestRoute)
    {
        if (!$requestRoute)
        {
            return $this->routeNotFound['dispatcher'];
        }

        $uri = $requestRoute[0];

        $uriLength = strlen($uri);
        $foundRoute = null;

        foreach ($this->routes as $route)
        {
            // look for exact url match or regexp search
            if (($route['url'] == $uri || ($uriLength > 1 && preg_match('@^'. $route['url'] .'$@', $uri))))
            {
                $foundRoute = $route;
            }
        }

        if (!$foundRoute)
        {
            return $this->routeNotFound['dispatcher'];
        }

        return $foundRoute['dispatcher'];
    }
}
