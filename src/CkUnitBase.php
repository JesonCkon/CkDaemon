<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/10/31
 * Time: 10:20
 */

namespace CkDaemon;


class CkUnitBase extends CkContainer
{
    public static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

}