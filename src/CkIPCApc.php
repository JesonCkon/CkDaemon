<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/11/7
 * Time: 15:55
 */

namespace CkDaemon;


class CkIPCApc extends CkIPCAbstract
{
    public $ttl = 86400;

    public function init()
    {
        return CkCommon::CheckExt("apc");
    }
    public function setTimeout($num)
    {
        $this->ttl = $num;
        return true;
    }

    public function get()
    {
        $path = null;
        $args = func_get_args();
        if (count($args) > 0) {
            $path = isset($args[ 0 ]) ? $args[ 0 ] : "";
        }
        return apc_fetch($path);
    }

    public function set()
    {
        $path = $content = null;
        $args = func_get_args();
        if (count($args) > 0) {
            $path = isset($args[ 0 ]) ? $args[ 0 ] : "";
            $content = isset($args[ 1 ]) ? $args[ 1 ] : null;
            $ttl = isset($args[ 2 ]) ? $args[ 2 ] : $this->ttl;
        }

        return apc_store($path, $content,$ttl);
    }

    public function del()
    {
        $path = $content = null;
        $args = func_get_args();
        if (count($args) > 0) {
            $path = isset($args[ 0 ]) ? $args[ 0 ] : "";
        }
        return apc_delete($path);
    }
}