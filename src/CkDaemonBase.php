<?php
namespace CkDaemon;

class CkDaemonBase
{
    private $info_dir = "/tmp"; //默认存放目录
    private $workers_count = 0; // 0则 独立执行任务
    private $gc_enabled = null;
    private $workers_max = 8; //最多运行8个进程

    public function __construct($user = 'nobody', $output = "/dev/null")
    {
        global $argc, $argv;
        $this->user = $user;//设置运行的用户 默认情况下nobody
        $this->output = $output; //设置输出的地方
        $this->argc = $argc;
        $this->argv = $argv;
        $this->init();

    }

    protected function usage()
    {
        printf("Usage: %s {start | stop | restart | status}\n", $this->argv[ 0 ]);
    }

    protected function init()
    {
        //初始化设置
        if (php_sapi_name() != "cli") {
            die("only run in cli\n");
        }
        if (function_exists('gc_enable')) {
            //启动GC PHP 5.3 UP
            gc_enable();
            $this->gc_enabled = gc_enabled();
        }
        $this->setUser();

    }

    protected function setUser()
    {
        $result = false;
        if (empty($this->user)) {
            return true;
        }
        $user = posix_getpwnam($this->user);
        var_dump($user);
        if ($user) {
            $uid = $user[ 'uid' ];
            $gid = $user[ 'gid' ];
            $result = posix_setuid($uid);
            posix_setgid($gid);
        }

        return $result;
    }
}
