<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 16/11/7
 * Time: 15:57
 */

namespace CkDaemon;

class CkIPC
{
    public $error_num = 0;
    public $error_message = "";

    public function __construct($drive = "apcu")
    {
        //shmop apc apcu
        $drives = array("shmop", "apc", "apcu");
        if (!in_array($drive, $drives)) {
            $this->error_message = "Instead of supporting $drive ";

            return false;
        }
        if (extension_loaded($drive)) {
            if ($drive == 'apcu') {
                return new CkIPCApcu();
            }
            if ($drive == 'apc') {
                return new CkIPCApc();
            }
            if ($drive == 'shmop') {
                return new CkIPCShmop();
            }
        }
    }
}