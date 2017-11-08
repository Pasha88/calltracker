<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class InsertOrderCommand  extends Command {

    private $insertOrderSQL = "insert into orders values(unhex(replace(?,'-','')),?,?,?,?,?,?,?,unhex(replace(?,'-','')))";
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $order = $this->args['order'];
        if ($stmt = $conn->prepare($this->insertOrderSQL)) {
             $stmt->bind_param("ssssdsiss", $order->orderId, $order->customerUid, $order->createDate, $order->orderDate, $order->sum, $order->currencyCode,
                $order->statusId, $order->confirmationUrl, $order->idempotenceKey);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_INSERT_CUSTOMER_ORDER->message);
        }
         return true;
    }
}