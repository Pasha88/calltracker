<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/order/OrderStatusesListCommand.php');


class OrderStatusRepo extends Repository {

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

    public function getAll() {
        $c = new OrderStatusesListCommand(null);
        return  $this->executeTransaction($c);
    }

}