<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class OrderListCommand  extends Command {

    private $getAllCustomerOrdersSQL = 'select * from order where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $orders = [];
        $orderId = null;
        $customerUid = null;
        $orderDate = null;
        $sum = null;

        if ($stmt = $conn->prepare($this->getAllCustomerOrdersSQL)) {
            $stmt->bind_params("s", $this->args['customerUid']);
            $stmt->bind_result($orderId, $customerUid, $orderDate, $sum);
            $stmt->execute();
            while($stmt->fetch() != false) {
                $order = new Order($orderId, $customerUid, $orderDate, $sum);
                array_push($orders, $order);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_CUSTOMER_ORDERS->message);
        }
        return $orders;
    }
}