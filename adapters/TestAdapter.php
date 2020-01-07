<?php
    namespace adapters;

    /**
     * Interface for phones adapters
     * Phone's adapters allow RaspiSMS to use a platform to communicate with a phone number.
     * Its an adapter between internal and external code, as an API, command line software, physical modem, etc.
     *
     * All Phone Adapters must implement this interface
     */
    class TestAdapter implements AdapterInterface
    {
        /**
         * Classname of the adapter
         */
        public static function meta_classname() : string { return __CLASS__; }

        /**
         * Name of the adapter.
         * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.)
         */
        public static function meta_name() : string { return 'Test'; }

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description() : string { return 'A test adaptater that do not actually send or receive any message.'; }
        
        /**
         * Description of the datas expected by the adapter to help the user. (e.g : A list of expecteds Api credentials fields, with name and value)
         */
        public static function meta_datas_help() : string { return 'No datas.'; } 

        /**
         * Does the implemented service support flash smss
         */
        public static function meta_support_flash() : bool { return true ; }


        /**
         * Phone number using the adapter
         */
        private $number;

        /**
         * Datas used to configure interaction with the implemented service. (e.g : Api credentials, ports numbers, etc.).
         */
        private $datas;


        /**
         * Path for the file to read sms as a json from
         */
        private $test_file_read = PWD_DATAS . '/test_read_sms.json'; 
        
        /**
         * Path for the file to write sms as a json in
         */
        private $test_file_write = PWD_DATAS . '/test_write_sms.json'; 

        
        /**
         * Adapter constructor, called when instanciated by RaspiSMS
         * @param string $number : Phone number the adapter is used for
         * @param json string $datas : JSON string of the datas to configure interaction with the implemented service
         */
        public function __construct (string $number, string $datas)
        {
            $this->number = $number;
            $this->datas = $datas;
        }
    
    
        /**
         * Method called to send a SMS to a number
         * @param string $destination : Phone number to send the sms to
         * @param string $text : Text of the SMS to send
         * @param bool $flash : Is the SMS a Flash SMS
         * @return mixed Uid of the sended message if send, False else
         */
        public function send (string $destination, string $text, bool $flash)
        {
            $uid = uniqid();

            $at = (new \DateTime())->format('Y-m-d H:i:s');
            file_put_contents($this->test_file_write, json_encode(['uid' => $uid, 'at' => $at, 'destination' => $destination, 'text' => $text, 'flash' => $flash]) . "\n", FILE_APPEND);

            return uniqid();
        }


        /**
         * Method called to read SMSs of the number
         * @return array : Array of the sms reads
         */
        public function read () : array
        {
            $file_contents = file_get_contents($this->test_file_read);

            //Empty file to avoid dual read
            file_put_contents($this->test_file_read, '');

            $smss = explode("\n", $file_contents);

            $return = [];

            foreach ($smss as $key => $sms)
            {
                $decode_sms = json_decode($sms, true);
                if (NULL === $decode_sms)
                {
                    continue;
                }

                $return[] = $decode_sms;
            }

            return $return;
        }
    }
