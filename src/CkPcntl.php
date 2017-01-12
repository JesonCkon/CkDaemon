<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 17/1/10
 * Time: 10:24
 */

namespace CkDaemon;


class CkPcntl
{
    private $pid_dirs = array();
    private $processName = '';
    private $isAble = false;
    private $jobs = array();
    public $jobs_return = array();
    public $isDaemon = false;
    public $runFuncTime = 0;
    private $php_cli_version = '';
    private $argv;
    private $argc;
    const _DS = DIRECTORY_SEPARATOR;

    public function __construct()
    {
        $this->argv = $GLOBALS[ "argv" ];
        $this->argc = $GLOBALS[ "argc" ];
        $run_script_name = $this->argv[ 0 ];
        $sys_default_pid_dir = dirname($run_script_name);
        if (!in_array($sys_default_pid_dir, $this->pid_dirs)) {
            array_push($this->pid_dirs, $sys_default_pid_dir);
            $this->processName = md5($run_script_name);
        }
        if (function_exists("pcntl_fork")) {
            $this->isAble = true;
        }
        list($version_passed, $php_version) = CkCommon::phpVersionCheck('5.4.0');
        if ($version_passed == false) {
            die("PHP version must be > 5.4.0");
        }

        $this->php_cli_version = $php_version;
    }

    public function addFunc()
    {
        $num_args = func_get_args();
        if ($num_args[ 0 ] instanceof \Closure) {
            $this->jobs[] = $num_args[ 0 ];
        }
    }

    private function forkWait()
    {
        $is_deamon = $this->isDaemon;
        declare(ticks = 1);
        if ($this->isAble == false) {
            die("pcntl model error or not exist!");
        }
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            if ($is_deamon == true) {
                exit();
            }
            pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            echo "子进程running  " . getmypid();
            $pid = getmypid();
            $this->registerPidFile($pid);
            $this->registerSignalHandler();
            declare (ticks = 1);
            if ($is_deamon == true) {
                while (true) {
                    $this->doExecute();
                    if ($this->runFuncTime > 0) {
                        sleep($this->runFuncTime);
                    }
                }
            } else {
                $this->doExecute();
            }
            exit(0);
        }
        exit(0);
    }

    function signalHandler($signal)
    {
        //echo "获取到信号灯  ".$signal;
        if ($signal == SIGINT) {
            //$this->stop();
            echo 'signal received SIGINT' . PHP_EOL;
            posix_kill(posix_getpid(), SIGINT);
        }
        if ($signal == SIGTERM) {
            exit();
        }
        if ($signal == SIGUSR1) {
            echo 'signal received SIGUSR1' . PHP_EOL;
        }
        if ($signal == SIGUSR2) {
            echo 'signal received SIGUSR2' . PHP_EOL;
        }
    }

    protected function doExecute()
    {
        foreach ($this->jobs as $k => $job) {
            if ($job instanceof \Closure) {
                if (strnatcasecmp($this->php_cli_version, '7.0.0') >= 0) {
                    $this->jobs_return[ $k ] = $job->call($this, $this);
                } else {
                    $this->jobs_return[ $k ] = \Closure::bind($job, $this, $this);
                }
            }
        }
    }

    protected function registerSignalHandler()
    {
        pcntl_signal(SIGINT, array($this, 'signalHandler'));
        pcntl_signal(SIGTERM, array($this, 'signalHandler'));
        pcntl_signal(SIGUSR1, array($this, 'signalHandler'));
        pcntl_signal(SIGUSR2, array($this, 'signalHandler'));
    }

    protected function registerPidFile($pid)
    {
        $pid_file = $this->createPidFile();
        if ($pid_file != false) {
            file_put_contents($pid_file, $pid);
        }
    }

    protected function createPidFile()
    {
        $default_dir = $this->pid_dirs[ 0 ];
        if (is_dir($default_dir) && is_writable($default_dir)) {
            $pid_file = $default_dir . self::_DS . $this->processName . ".pid";
        } else {
            return false;
        }

        return $pid_file;
    }

    protected static function getPidByFile($filename)
    {
        if (file_exists($filename)) {
            return intval(file_get_contents($filename));
        } else {
            return 0;
        }
    }

    public function stop()
    {
        $pid_file = $this->createPidFile();
        $pid = self::getPidByFile($pid_file);
        if ($pid > 0) {
            posix_kill($pid, SIGTERM);
        }
    }

    public function status()
    {
        $pid_file = $this->createPidFile();
        $pid = self::getPidByFile($pid_file);
        if ($pid > 0) {
            $is_running = posix_getpgid($pid);
            if ($is_running == true) {
                echo "进程PID : " . $pid . " is Running\n";
            }
        }
    }

    public function setProcessName($name)
    {
        $this->processName = $name;
    }

    public function restart()
    {
    }

    public function start()
    {
        $this->forkWait();
    }

    protected function usage()
    {
        printf("Usage: %s {start | stop | restart | status}\n", $this->argv[ 0 ]);
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
}