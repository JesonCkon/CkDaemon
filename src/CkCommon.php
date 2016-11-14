<?php
/**
* Created by PhpStorm.
* User: kontem
* Date: 16/10/31
* Time: 10:25
*/

namespace CkDaemon;


class CkCommon
{
    public static function CheckExt($name = null)
    {
        if(empty($name)){
            return false;
        }
        return extension_loaded($name);
    }
}