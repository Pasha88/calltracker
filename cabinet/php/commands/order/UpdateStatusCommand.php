<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class UpdateStatusCommand  extends Command {

    private $updateOrderSQL = 'update orders set status = ? where order_id = unhex(replace(?,\'-\',\'\'))';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $orderId = $this->args['orderId'];
        $statusId = $this->args['statusId'];
        if ($stmt = $conn->prepare($this->updateOrderSQL)) {
            $stmt->bind_param("is", $statusId, $orderId);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_CUSTOMER_ORDER_STATUS->message);
        }
        return true;
    }
}