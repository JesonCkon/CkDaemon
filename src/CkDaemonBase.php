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
    protected $jobs_list_config = array();
    protected $jobs_return = array();
    protected $units;

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

    public function setJobs()
    {
        $num_args = func_get_args();
        $max_job_index = count($this->jobs_list) + 1;
        if (isset($num_args[ 0 ])) {
            if ($num_args[ 0 ] instanceof \Closure) {
                $this->jobs_list[ $max_job_index ] = $num_args[ 0 ];
                if (isset($num_args[ 1 ])) {
                    $this->jobs_list_config[ $max_job_index ] = $num_args[ 1 ];
                }
            }
        } else {
            //return $this->
        }
    }

    public function setUnits(CkUnitBase $ckUnitBase)
    {
        $this->units = $ckUnitBase;
    }

    public function getTransmit()
    {
        return $this->transmit;
    }

    protected function start()
    {
        $this->_log("---- process start ----");

        foreach ($this->jobs_list as $index => $job) {
            $obj = $this->jobs_list[ $index ];
            if ($obj instanceof \Closure) {
                $temp_job_conf = isset($this->jobs_list_config[ $index ]) ? $this->jobs_list_config[ $index ] : null;
                //$this->jobs_return[ $index ] = $obj->call($this, $this);
                $this->createProcess($obj, $temp_job_conf);
            }
        }
    }

    protected function createProcess($func_object, $config)
    {

        $count = $config[ 'workers' ];
        while (true) {
            if (function_exists('pcntl_signal_dispatch')) {

                pcntl_signal_dispatch();
            }
            $pid = -1;
            if ($this->workers_count < $count) {
                echo $pid = pcntl_fork();
            }
            if ($pid > 0) {
                $this->workers_count++;

            } elseif ($pid == 0) {

                // 这个符号表示恢复系统对信号的默认处理
                pcntl_signal(SIGTERM, SIG_DFL);
                pcntl_signal(SIGCHLD, SIG_DFL);
                if ($func_object instanceof \Closure) {
                    $this->_log("---- " . $this->workers_count . " start ----");
                    $func_object->call($this, $this);
                    $this->_log("---- " . $this->workers_count . " end ----");
                }

                return;

            } else {
                $this->_log("---- process end ----");
                exit(0);
            }
        }
        exit(0);
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

    private function _log($message)
    {
        printf("%s\t%d\t%d\t%s\n", date("c"), posix_getpid(), posix_getppid(), $message);
    }
}
