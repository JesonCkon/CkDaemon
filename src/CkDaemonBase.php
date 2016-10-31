<?php
namespace CkDaemon;

class CkDaemonBase
{
    private $info_dir = "/tmp"; //默认存放目录
    private $workers_count = 0; // 0则 独立执行任务
    private $gc_enabled = null;
    private $user_info = null;
    private $workers_max = 8; //最多运行8个进程
    public $error_message = '';
    public $init_status = false;
    protected $jobs_list = array();

    public function __construct($user = 'nobody', $output = "/dev/null")
    {
        global $argc, $argv;
        $this->user = $user;//设置运行的用户 默认情况下nobody
        $this->output = $output; //设置输出的地方
        $this->argc = $argc;
        $this->argv = $argv;
        $this->init_status = $this->init();

    }

    public function setInfoDir($path)
    {
        $this->info_dir = $path;
    }

    public function setWorkers($func, $workers_count)
    {
        $this->workers_count = $workers_count;
        $this->setJobs($func);
    }

    public function setMaxWorkersNum($num)
    {
        $this->workers_max = intval($num);
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
        $this->user_info = self::setUser($this->user);
        if ($this->user_info[ 'is_set_user' ] == true) {
            chdir("/");

            return true;
        } else {
            $this->error_message = '程序操作用户设置失败 posix_getpwnam ' . $this->user . "";
        }

        return false;
    }

    protected static function setUser($name)
    {
        $result = [];
        $result[ 'is_set_user' ] = false;
        $result[ 'is_set_group' ] = false;
        $is_set_user = false;
        if (empty($name)) {
            return true;
        }
        $user = posix_getpwnam($name);
        if ($user) {
            $result[ 'uid' ] = $user[ 'uid' ];
            $result[ 'gid' ] = $user[ 'gid' ];
            $result[ 'is_set_user' ] = posix_setuid($result[ 'uid' ]);
            $result[ 'is_set_group' ] = posix_setgid($result[ 'gid' ]);
            $group_info = posix_getgrgid($result[ 'gid' ]);
            $result[ 'group_name' ] = $group_info[ 'name' ];
        }

        return $is_set_user;
    }

    protected function setJobs()
    {
        $num_args = func_num_args();
        if (isset($num_args[0])) {
            if($num_args[0] instanceof \Closure){
                $this->jobs_list[] = $num_args[0];
            }
        }else{
            //return $this->
        }
    }

    protected function start()
    {
    }

    protected function stop()
    {
    }

    protected function restart()
    {
    }

    protected function status()
    {
    }

    public function run()
    {
        //print_r($this->argv);
        if ($this->argc != 2) {
            $this->usage();
        } else {
            if ($this->argv[ 1 ] == 'start') {
                $this->start();
            } else if ($this->argv[ 1 ] == 'stop') {
                $this->stop();
            } else if ($this->argv[ 1 ] == 'restart') {
                $this->restart();
            } else if ($this->argv[ 1 ] == 'status') {
                $this->status();
            } else {
                $this->usage();
            }
        }
    }

}
