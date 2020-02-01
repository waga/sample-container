<?php

namespace App;

use App\Config;

class View
{
    protected $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function display($file, array $variables = array())
    {
        foreach ($variables as $varName => $varValue)
        {
            $$varName = $varValue;
        }
        include $this->dir . $file;
        return $this;
    }
}
