<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class UpdateOrderCommand  extends Command {

    private $updateOrderSQL = 'update orders set customer_uid = ?, order_date = ?, sum = ?, currency_code = ?, tariff_id = ?, 
                                  status = ?, confirmation_url = ?, idempotence_key = unhex(replace(?,\'-\',\'\')) where order_id = unhex(replace(?,\'-\',\'\'))';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $order = $this->args['order'];
        if ($stmt = $conn->prepare($this->updateOrderSQL)) {
            $stmt->bind_param("ssdsisss", $order->customerUid, $order->orderDate, $order->sum, $order->currencyCode,
                $order->statusId, $order->confirmationUrl, $order->idempotenceKey, $order->orderId);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_CUSTOMER_ORDER->message);
        }
        return true;
    }
}