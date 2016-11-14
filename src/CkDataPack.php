<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/11/7
 * Time: 16:53
 */

namespace CkDaemon;


class CkDataPack
{
    //大小端判断
    public static function IsBigEndian()
    {
        $bin = pack("L", 0x12345678);
        $hex = bin2hex($bin);
        if (ord(pack("H2", $hex)) === 0x78) {
            return false;
        }

        return true;
    }

    //基础协议 默认
    public static function resolution_protocol($bin, $parse = "a6protocol/ilen/a*content/")
    {
        $data = unpack($parse, $bin);

        return $data;
    }

    public static function combine_protocol($content, $parse = "a6ia*")
    {
        $bin = pack($parse, "json", strlen($content), $content);

        return $bin;
    }

}