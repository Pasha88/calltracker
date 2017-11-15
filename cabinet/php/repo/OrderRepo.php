<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/order/OrderListCommand.php');
require_once(dirname(__DIR__) . '/commands/order/InsertOrderCommand.php');
require_once(dirname(__DIR__) . '/commands/order/OrderByIdCommand.php');

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

    static public function getTestInstance() {
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

    public function updateOrderStatus($orderId, $statusId) {
        $params = array('orderId' => $orderId, 'statusId' => $statusId);
        $c = new UpdateStatusCommand($params);
        return  $this->executeTransaction($c);
    }

    public function getOrderById($paymentid) {
        $params = array('id' => $paymentid);
        $c = new OrderByIdCommand($params);
        return  $this->executeTransaction($c);
    }

}

//$r = '{"type":"notification","event":"payment.waiting_for_capture","object":{"id":"2197ac43-000f-500a-b000-0ab8331ac214","status":"waiting_for_capture","paid":true,"amount":{"value":"777.00","currency":"RUB"},"created_at":"2017-11-10T12:16:03.301Z","expires_at":"2017-11-17T12:16:16.327Z","metadata":{},"payment_method":{"type":"bank_card","id":"2197ac43-000f-500a-b000-0ab8331ac214","saved":false,"card":{"last4":"1026","expiry_month":"11","expiry_year":"2020","card_type":"Unknown"},"title":"Bank card *1026"},"recipient":{"account_id":"500100","gateway_id":"1500100"}}}';
//$payment = json_decode($r);
//$t=0;