<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/order/OrderListCommand.php');
require_once(dirname(__DIR__) . '/commands/order/InsertOrderCommand.php');


class OrderRepo extends Repository {

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

    public function orderList($filters) {
        $params = array('filters' => $filters);
        $c = new OrderListCommand($params);
        return  $this->executeTransaction($c);
    }

    public function insertOrder($order) {
        $params = array('order' => $order);
        $c = new InsertOrderCommand($params);
        return  $this->executeTransaction($c);
    }

    public function updateOrder($order) {
        $params = array('order' => $order);
        $c = new UpdateOrderCommand($params);
        return  $this->executeTransaction($c);
    }
}