<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/FindAllNumberPoolCommand.php');
require_once(dirname(__DIR__) . '/commands/FindNumberPoolCommand.php');
require_once(dirname(__DIR__) . '/commands/SaveNumberPoolCommand.php');

class PhoneNumberPoolRepo extends Repository {

    private static $_instance = null;

    private function __construct() {}
    protected function __clone() {}

    static public function getInstance() {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getPhoneNumberPool($id) {
        $params = array('id' => $id);
        $c = new FindNumberPoolCommand($params);
        return  $this->executeTransaction($c);
    }

    public function getAllNumbers() {
        $c = new FindAllNumberPoolCommand(null);
        return  $this->executeTransaction($c);
    }

    public function savePhoneNumberPool($phoneNumberPool) {
        $params = array('id' => $phoneNumberPool->id, 'number' => $phoneNumberPool->number, 'description' => $phoneNumberPool->description,
            'freeDateTime' => $phoneNumberPool->freeDateTime, 'customerId' => $phoneNumberPool->customerId);
        $c = new SaveNumberPoolCommand($params);
        return  $this->executeTransaction($c);
    }

}