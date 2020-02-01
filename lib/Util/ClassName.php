<?php

namespace App\Util;

class ClassName
{
    const SEGMENT_SEPARATOR = '\\';

    public static function getBase($className)
    {
        if (false === strpos($className, self::SEGMENT_SEPARATOR))
        {
            return '';
        }
        list(, $baseName) = explode(self::SEGMENT_SEPARATOR, $className);
        return $baseName;
    }
}
