<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 17/1/10
 * Time: 10:08
 */

namespace CkDaemon;


class CkDataMergeEngine
{
    private $pid_file_dirs = array();

    public function addPidDir($dirname = null)
    {
        if (is_string($dirname)
            && is_dir($dirname)
            && !in_array($dirname, $this->pid_file_dirs)) {
            array_push($this->pid_file_dirs, $dirname);
        }elseif(is_array($dirname)){
            foreach($dirname as $key => $item_dirname){
                if(is_dir($item_dirname) && !in_array($item_dirname,$this->pid_file_dirs)){
                    array_push($this->pid_file_dirs, $dirname);
                }
            }
        }
    }

    public function initService()
    {
        CkLog::message(__CLASS__ . __FUNCTION__ . " init >>>");
        $pc_service = new CkPcntl();
        $pc_service->forkWait(true);
    }
}