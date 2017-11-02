<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class SaveOrderCommand  extends Command {

    private $updateOrderSQL = 'update order set customer_uid = ?, order_date = ?, sum = ? where order_id = ?';
    private $insertOrderSQL = 'insert into order values(?,?,?,?)';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $order = $this->args['order'];

        if(isset($order->orderId)) {
            if ($stmt = $conn->prepare($this->updateOrderSQL)) {
                $stmt->bind_params("ssd", $order->customerUid, $order->orderDate, $order->sum, $order->orderId);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_CUSTOMER_ORDER->message);
            }
        }
        else {
            if ($stmt = $conn->prepare($this->insertOrderSQL)) {
                $stmt->bind_params("issd", $order->orderId, $order->customerUid, $order->orderDate, $order->sum);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_INSERT_CUSTOMER_ORDER->message);
            }
        }
        return true;
    }
}