<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/cash/CashOperListCommand.php');
require_once(dirname(__DIR__) . '/commands/cash/SaveCashOperCommand.php');


class BalanceOper extends Repository {

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

    public function orderList($customerUid) {
        $params = array('customerUid' => $customerUid);
        $c = new OrderListCommand($params);
        return  $this->executeTransaction($c);
    }

    public function saveOrder($order) {
        $params = array('order' => $order);
        $c = new InsertOrderCommand($params);
        return  $this->executeTransaction($c);
    }

}