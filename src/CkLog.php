<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 17/1/10
 * Time: 10:16
 */

namespace CkDaemon;


class CkLog
{
    public static function message()
    {
        $num_args = func_get_args();
        if (isset($num_args[ 0 ])) {
            echo self::toString($num_args[ 0 ]);
        }
    }

    private static function toString($message)
    {
        if(is_string($message)){
            return $message;
        }
        if(is_array($message)){
            return json_encode($message);
        }
        if(is_object($message)){
            return (string)$message;
        }
        return $message;
    }

}