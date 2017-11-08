<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/OrderStatus.php');

class OrderStatusByCodeCommand  extends Command {

    private $getAllOrderStatusesSQL = 'select * from order_status where code = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $orderStatus = null;
        $orderStatusId = null;
        $orderStatusName= null;
        $code = null;

        if ($stmt = $conn->prepare($this->getAllOrderStatusesSQL)) {
            $stmt->bind_param("s", $this->args['code']);
            $stmt->bind_result($orderStatusId, $code, $orderStatusName);
            $stmt->execute();
            if($stmt->fetch() != false) {
                $orderStatus = new OrderStatus($orderStatusId, $code, $orderStatusName);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_STATUS_BY_CODE->message);
        }
        return $orderStatus;
    }
}