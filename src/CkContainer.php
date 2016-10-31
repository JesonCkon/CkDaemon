<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/10/31
 * Time: 10:25
 */

namespace CkDaemon;


class CkContainer
{
    private $s = array();
    public $di_objects = array();
    private $cache_list = array();
    public function __set($k, $c)
    {
        if (!in_array($k, $this->di_objects)) {
            $this->di_objects[] = $k;
        }
        if (!is_object($c)) {
            $this->$k = $c;
        } else {
            $this->s[$k] = $c;
        }
    }
    public function __get($k)
    {
        if (empty($k)) {
            return null;
        }
        if (isset($this->s[$k])) {
            #return $this->s[$k]($this);
            $temp_cache_obj = $this->_getCache($k);
            if ($temp_cache_obj == false) {
                $func =(string)$this->s[$k];
                $obj = $func($this);
                $this->_setCache($k, $obj);

                return $obj;
            } else {
                return $temp_cache_obj;
            }
        }

        return null;
    }
    private function _getCache($key)
    {
        if (isset($this->cache_list[$key])) {
            return $this->cache_list[$key];
        }

        return false;
    }
    private function _setCache($key, $value)
    {
        $this->cache_list[$key] = $value;

        return true;
    }
}