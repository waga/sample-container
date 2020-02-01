<?php

namespace App\Util\Web
{
    class Dump
    {
    }
}

namespace
{
    if (!function_exists('d'))
    {
        function d()
        {
            foreach (func_get_args() as $a)
            {
                print_r($a);
                echo PHP_EOL;
            }
        }
    }

    if (!function_exists('v'))
    {
        function v()
        {
            foreach (func_get_args() as $a)
            {
                var_dump($a);
                echo PHP_EOL;
            }
        }
    }
}
