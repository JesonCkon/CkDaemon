<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/11/9
 * Time: 16:16
 */

namespace CkDaemon;


abstract class CkIPCAbstract
{
    abstract public function init();
    abstract public function get();
    abstract public function set();
    abstract public function del();
}