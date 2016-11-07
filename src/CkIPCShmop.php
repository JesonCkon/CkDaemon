<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/11/2
 * Time: 17:51
 */

namespace CkDaemon;


class CkIPCShmop
{
    protected $resource = array();

    public function init($path, $size = 1024)
    {
        if (!function_exists('ftok') || !function_exists('shmop_open')) {
            return false;
        }
        $shmop_key = ftok($path, 't');
        $shmop_id = shmop_open($shmop_key, "c", 0755, $size);
        $this->resource[ md5($path) ] = array("shmop_key" => $shmop_key, "shmop_id" => $shmop_id);
    }

    public function get($path)
    {
        $resource_info = $this->resource[ md5($path) ];

        $shmop_key = $resource_info[ 'shmop_key' ];

        $resource_id = shmop_open($shmop_key,'a',0,0);
        $size = shmop_size($resource_id);

        $content = shmop_read($resource_id, 0, $size);
        shmop_close($resource_id);

        return $content;
    }

    public function set_all($path, $content)
    {
        $resource_info = $this->resource[ md5($path) ];

        $shmop_key = $resource_info[ 'shmop_key' ];
        $resource_id = shmop_open($shmop_key, 'c', 0755, strlen($content));
        $result = shmop_write($resource_id, $content, 0);
        shmop_close($resource_id);

        return $result != false ? true : false;
    }

    public function set_add($path, $content)
    {
        $resource_info = $this->resource[ md5($path) ];

        $shmop_key = $resource_info[ 'shmop_key' ];

        //echo strlen(trim($this->get($path)));
        $start = strlen(trim($this->get($path)));

        $resource_id = shmop_open($shmop_key, 'w', 0755, strlen($content));
        //var_dump($size, $write_size);
        $result = shmop_write($resource_id, $content, $start);
        shmop_close($resource_id);

        return $result != false ? true : false;
    }

    public function del($key)
    {
        $resource_id = $this->resource[ md5($key) ];
        shmop_delete($resource_id);
        shmop_close($resource_id);
    }
}