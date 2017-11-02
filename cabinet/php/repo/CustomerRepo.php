<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/customer/FindCustomerCommand.php');
require_once(dirname(__DIR__) . '/commands/customer/FindCustomerCommandByEmail.php');
require_once(dirname(__DIR__) . '/commands/customer/FindCustomerCommandByUid.php');
require_once(dirname(__DIR__) . '/commands/customer/SaveCustomerByUidCommand.php');


class CustomerRepo extends Repository {

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

    public function getCustomer($customerId) {
        $params = array('customerId' => $customerId);
        $c = new FindCustomerCommand($params);
        return  $this->executeTransaction($c);
    }

    public function getCustomerByEmail($email) {
        $params = array('email' => $email);
        $c = new FindCustomerCommandByEmail($params);
        return  $this->executeTransaction($c);
    }

    public function getCustomerByUid($uid) {
        $params = array('customerUid' => $uid);
        $c = new FindCustomerCommandByUid($params);
        return  $this->executeTransaction($c);
    }

    public function saveCustomer($customer) {
        $c = new SaveCustomerByUidCommand($customer);
        return  $this->executeTransaction($c);
    }
}