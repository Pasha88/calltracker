<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/OrderStatus.php');

class OrderStatusesListCommand  extends Command {

    private $getAllOrderStatusesSQL = 'select * from order_status';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $statuses = [];
        $orderStatusId = null;
        $orderStatusName= null;
        $code = null;

        if ($stmt = $conn->prepare($this->getAllOrderStatusesSQL)) {
            $stmt->bind_result($orderStatusId, $code, $orderStatusName);
            $stmt->execute();
            while($stmt->fetch() != false) {
                $orderStatus = new OrderStatus($orderStatusId, $code, $orderStatusName);
                $statuses[$code] = $orderStatus;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_ALL_ORDER_STATUSES->message);
        }
        return $statuses;
    }
}