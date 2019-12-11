<?php

namespace daemons;

/**
 * Class defining the global structur of a Linux Daemon
 */
abstract class AbstractDaemon
{
	protected $name;
	protected $uniq;
	protected $logger;
	private $is_running = true;
	private $signals = array (
        SIGTERM,
        SIGINT,
        SIGCHLD,
        SIGHUP 
    );

	/**
	 * Class used to handle POSIX signals and fork from the current process
	 *
     * @param string $name : The name of the class
     * @param object $logger : A PSR3 logger instance
     * @param string $pid_dir : Directory for the pid files
     * @param array $signals :An array containing additional POSIX signals to handle [optionel]
     * @param bool $uniq : Must the process be uniq ?
	 */
    protected function __construct (string $name, object $logger, string $pid_dir = '/var/run', array $signals = [], bool $uniq = false)
    {
        $this->name = $name;
        $this->logger = $logger;
        $this->signals = array_merge($this->signals, $signals);
        $this->uniq = $uniq;
        $this->pid_dir = $pid_dir;

        //Allow script to run indefinitly
        set_time_limit(0);

        //Register signals
		$this->register_signals();
    }


	/**
	 * Used to register POSIX signals
	 */
    private function register_signals()
    {
        //Enable a tick at every 1 instruction, allowing us to run a function frequently, for exemple looking at signal status
        declare(ticks = 1);

        foreach ($this->signals as $signal)
        {
            //For each signal define the method handle_signal of the current class as the way to handle it
            @pcntl_signal($signal, [
                    'self',
					'handle_signal' 
            ]);
		}
    }

	/**
	 * Used to handle properly SIGINT, SIGTERM, SIGCHLD and SIGHUP
	 *
     * @param int $signal
     * @param mixed $signinfo
	 */
    protected function handle_signal(int $signal, $signinfo)
    {
        if ($signal == SIGTERM || $signal == SIGINT) //Stop the daemon
        {
			$this->is_running = false;
        }
        else if ($signal == SIGHUP) //Restart the daemon
        {
			$this->on_stop();
			$this->on_start();
        }
        else if ($signal == SIGCHLD) //On daemon child stopping
        {
			pcntl_waitpid(-1, $status, WNOHANG);
        }
        else //All the other signals
        {
			$this->handle_other_signals($signal);
		}
    }


	/**
	 * Launch the infinite loop executing the "run" abstract method
	 */
    protected function start ()
    {
        //If process must be uniq and a process with the same pid file is already running
        if (file_exists($this->pid_dir . '/' . $this->name . '.pid') && $this->uniq)
        {
            echo "Another process named " . $this->name . " is already running.\n";
            return false;
        }

        $pid = pcntl_fork(); //Fork current process into a child, so we will be able to later make the child indepedant, kill current process and keep only the child

        if ($pid == -1) //Impossible to run script
        {
            echo "Impossible to create a subprocess.\n";
            return false;
        }
        elseif ($pid) //Current script
        {
            echo "Child process started with pid " . $pid . ".\n";
            return true;
        }

        //Child script
        $sid = posix_setsid(); //Try to make the child process a main process
        if ($sid == -1) //Error
        {
            $this->logger->critical('Cannot make the child process independent.');
            exit(1);
        }

        //Create pid dir if not exists
        if (!file_exists($this->pid_dir))
        {
            $success = mkdir($this->pid_dir, 0777, true);
            if (!$success)
            {
                $this->logger->critical('Cannot create PID directory : ' . $this->pid_dir);
                exit(2);
            }
        }

        //Set process name
        cli_set_process_title($this->name);

        //Write the pid of the process into a file
        file_put_contents($this->pid_dir . '/' . $this->name . '.pid', getmypid());

        $this->on_start();
        
        try 
        {
            while ($this->is_running)
            {
                pcntl_signal_dispatch(); //Call dispatcher for signals
                $this->run();
            }
        }
        catch (\Exception $e)
        {
            $this->logger->critical('Exception : ' . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
        }
        
        $this->on_stop();

        //Delete pid file
        if (file_exists($this->pid_dir . '/' . $this->name . '.pid'))
        {
            unlink($this->pid_dir . '/' . $this->name . '.pid');
        }
    }


	/**
	 * True if the daemon is running
	 */
    public function is_running()
    {
		return $this->is_running;
    }

	/**
	 * Override to implement the code that run infinetly (actually, it run one time but repeat the operation infinetly
	 */
    protected abstract function run();


	/**
	 * Override to execute code before the ''run'' method on daemon start
	 */
    protected abstract function on_start();


	/**
	 * Override to execute code after the ''run'' method on daemon shutdown
	 */
	protected abstract function on_stop();
    
    
    /**
	 * Override to handle additional POSIX signals
	 *
	 * @param int $signal : Signal sent by interrupt
	 */
	protected abstract function handle_other_signals(int $signal);
}
