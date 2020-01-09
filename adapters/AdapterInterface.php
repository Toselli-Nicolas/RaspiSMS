<?php
    namespace adapters;

    /**
     * Interface for phones adapters
     * Phone's adapters allow RaspiSMS to use a platform to communicate with a phone number.
     * Its an adapter between internal and external code, as an API, command line software, physical modem, etc.
     *
     * All Phone Adapters must implement this interface
     */
    interface AdapterInterface
    {
        /**
         * Classname of the adapter
         */
        public static function meta_classname() : string;

        /**
         * Name of the adapter.
         * It should probably be the name of the service it adapt (e.g : Gammu SMSD, OVH SMS, SIM800L, etc.)
         */
        public static function meta_name() : string;

        /**
         * Description of the adapter.
         * A short description of the service the adapter implements.
         */
        public static function meta_description() : string;
        
        /**
         * Description of the datas expected by the adapter to help the user. (e.g : A list of expecteds Api credentials fields, with name and value)
         */
        public static function meta_datas_help() : string;
        
        /**
         * List of entries we want in datas for the adapter
         * @return array : Eachline line is a field as an array with keys : name, title, description, required
         */
        public static function meta_datas_fields() : array;

        /**
         * Does the implemented service support flash smss
         */
        public static function meta_support_flash() : bool;
        
        /**
         * Does the implemented service support status change
         */
        public static function meta_support_status_change() : bool;


        /**
         * Adapter constructor, called when instanciated by RaspiSMS
         * @param string $number : Phone number the adapter is used for
         * @param json string $datas : JSON string of the datas to configure interaction with the implemented service
         */
        public function __construct (string $number, string $datas);
    
    
        /**
         * Method called to send a SMS to a number
         * @param string $destination : Phone number to send the sms to
         * @param string $text : Text of the SMS to send
         * @param bool $flash : Is the SMS a Flash SMS
         * @return mixed Uid of the sended message if send, False else
         */
        public function send (string $destination, string $text, bool $flash = false);


        /**
         * Method called to read unread SMSs of the number
         * @return array : Array of the sms reads
         */
        public function read () : array;
        
        
        /**
         * Method called to verify if the adapter is working correctly
         * should be use for exemple to verify that credentials and number are both valid
         * @return boolean : False on error, true else
         */
        public function test () : bool;
        
        
        /**
         * Method called on reception of a status update notification for a SMS
         * @return mixed : False on error, else array ['uid' => uid of the sms, 'status' => New status of the sms ('unknown', 'delivered', 'failed')]
         */
        public static function status_change_callback ();
    }
