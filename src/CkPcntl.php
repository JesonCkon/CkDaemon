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
    private $isAble = false;
    private $jobs = array();
    public $jobs_return = array();
    private $php_cli_version = '';

    public function __construct()
    {
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

    public function forkWait($is_deamon = false)
    {
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
            $this->registerSignalHandler();
            declare (ticks = 1);
            if ($is_deamon == true) {
                while (true) {
                    $this->doExecute();
                }
            } else {
                $this->doExecute();
            }
        }
    }

    function signalHandler($signal)
    {
        //echo "获取到信号灯  ".$signal;
        if ($signal == SIGINT) {
            posix_kill(posix_getpid(), SIGINT);
        }
        if ($signal == SIGTERM) {
            posix_kill(posix_getpid(), SIGTERM);
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
}