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
    public static function phpVersionCheck( $version = '5.4.0' ) {
        $php_version = explode( '-', phpversion() );
        // =0表示版本为5.0.0  ＝1表示大于5.0.0  =-1表示小于5.0.0
        $is_pass = strnatcasecmp( $php_version[0], $version ) >= 0 ? true : false;

        return array($is_pass,$php_version[0]);
    }
}