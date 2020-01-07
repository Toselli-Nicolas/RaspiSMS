<?php
namespace daemons;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

/**
 * Main daemon class
 */
class Manager extends AbstractDaemon
{
    private $internal_phone;
    private $internal_scheduled;
    private $internal_received;
    private $bdd;

    public function __construct()
    {
        $logger = new Logger('Daemon Manager');
        $logger->pushHandler(new StreamHandler(PWD_LOGS . '/raspisms.log', Logger::DEBUG));

        $name = "RaspiSMS Daemon Manager";
        $pid_dir = PWD_PID;
        $additional_signals = [];
        $uniq = true; //Main server should be uniq

        //Construct the server and add SIGUSR1 and SIGUSR2
        parent::__construct($name, $logger, $pid_dir, $additional_signals, $uniq);

        parent::start();
    }


    public function run()
    {
        //Create the internal controllers
        $this->bdd = \descartes\Model::_connect(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, 'UTF8');
        $this->internal_phone = new \controllers\internals\Phone($this->bdd);


        $this->start_sender_daemon();

        $this->start_webhook_daemon();

        $phones = $this->internal_phone->get_all();
        $this->start_phones_daemons($phones);

        sleep(1);
    }
    
    
    /**
     * Function to start sender daemon
     * @return void
     */
    public function start_sender_daemon ()
    {
        $name = 'RaspiSMS Daemon Sender';
        $pid_file = PWD_PID . '/' . $name . '.pid';
        
        if (file_exists($pid_file))
        {
            return false;
        }

        //Create a new daemon for sender
        exec('php ' . PWD . '/console.php controllers/internals/Console.php sender > /dev/null &');
    }
    
    
    /**
     * Function to start webhook daemon
     * @return void
     */
    public function start_webhook_daemon ()
    {
        $name = 'RaspiSMS Daemon Webhook';
        $pid_file = PWD_PID . '/' . $name . '.pid';
        
        if (file_exists($pid_file))
        {
            return false;
        }

        //Create a new daemon for webhook
        exec('php ' . PWD . '/console.php controllers/internals/Console.php webhook > /dev/null &');
    }


    /**
     * Function to start phones daemons
     * @param array $phones : Phones to start daemon for if the daemon is not already started
     * @return void
     */
    public function start_phones_daemons (array $phones)
    {
        foreach ($phones as $phone)
        {
            $phone_name = 'RaspiSMS Daemon Phone ' . $phone['number'];
            $pid_file = PWD_PID . '/' . $phone_name . '.pid';
            
            if (file_exists($pid_file))
            {
                continue;
            }

            //Create a new daemon for the phone
            exec('php ' . PWD . '/console.php controllers/internals/Console.php phone --id_phone=\'' . $phone['id'] . '\' > /dev/null &');
        }
    }


    public function on_start()
    {
        $this->logger->info("Starting Manager with pid " . getmypid());
    }


    public function on_stop() 
    {
        $this->logger->info("Stopping Manager with pid " . getmypid ());
    }


    public function handle_other_signals($signal)
    {
        $this->logger->info("Signal not handled by " . $this->name . " Daemon : " . $signal);
    }
}
