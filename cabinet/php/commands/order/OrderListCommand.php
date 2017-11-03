<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');

class OrderListCommand  extends Command {

    private $getAllCustomerOrdersSQL = 'select * from order where customer_uid = ? and ';
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
        $tariffId = null;
        $status = null;
        $customerEmail = null;
        $orderStatusName = null;
        $tariffName = null;

        $filters = $this->args['filters'];
        $sqlMeta = $this->createGetOrdersSQL();

        if ($stmt = $conn->prepare($sqlMeta->sql)) {
            $stmt->bind_param($sqlMeta->bindString,
                $filters->customerUid, $filters->customerUid,
                $filters->orderId, $filters->orderId,
                $filters->customerEmail, $filters->customerEmail,
                $filters->orderStatusId, $filters->orderStatusId,
                $filters->sumFrom, $filters->sumFrom,
                $filters->sumTo, $filters->sumTo,
                $filters->orderDateFrom, $filters->orderDateFrom,
                $filters->orderDateTo, $filters->orderDateTo
            );
            $stmt->bind_result($orderId, $customerUid, $orderDate, $sum, $tariffId, $status, $customerEmail, $orderStatusName, $tariffName);
            $stmt->execute();
            while($stmt->fetch() != false) {
                $order = new Order($orderId, $customerUid, $orderDate, $sum, $tariffId, $status, $customerEmail, $orderStatusName, $tariffName);
                array_push($orders, $order);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_ORDERS->message);
        }
        return $orders;
    }

    private function createGetOrdersSQL() {
        $result = new stdClass();
        $orderParam = 'order_date';
        $orderAsc = 'DESC';
        $sql = "select o.*, c.email, os.dsc, t.tariff_name from orders o join customer c on c.customer_uid = o.customer_uid 
                                        join order_status os on os.order_status_id = o.status 
                                        join tariff t on t.tariff_id = o.tariff_id where ";
        $sql .= " (o.customer_uid = ? or ? is null)";
        $sql .= " and (o.order_id = ? or ? is null)";
        $sql .= " and (c.email = ? or ? is null)";
        $sql .= " and (o.status = ? or ? is null)";
        $sql .= " and (o.sum >= ? or ? is null)";
        $sql .= " and (o.sum <= ? or ? is null)";
        $sql .= " and (datediff(o.order_date, ?) >= 0 or ? is null)";
        $sql .= " and (datediff(o.order_date, ?) <= 0 or ? is null)";

        $bindString = "ssiissiiddddssss";

        if(isset($page)) {
            if(!isset($size)) {
                $size = 25;
            }
            $sql .= " ORDER BY " . $orderParam . " " . $orderAsc;
            $sql .= " LIMIT " . $page*$size . "," . $size;
        }

        $result->sql = $sql;
        $result->bindString = $bindString;
        return  $result;
    }
}
