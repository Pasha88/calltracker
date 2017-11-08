<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Order.php');
require_once(dirname(__DIR__) . '/../util/Util.php');

class OrderListCommand  extends Command {

    private $orderCountSQL = 'select count(o.order_id) as cnt  from orders o where o.customer_uid = ? or ? is null';
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
        $createDate = null;
        $orderDate = null;
        $sum = null;
        $currencyCode = null;
        $status = null;
        $statusCode = null;
        $customerEmail = null;
        $orderStatusName = null;
        $tariffName = null;
        $confirmationUrl = null;
        $idempotenceKey = null;

        $filters = $this->args['filters'];
        $sqlMeta = $this->createGetOrdersSQL();

        $page = $filters->page;
        $size = $filters->size;

        $df = Util::createCommonDate($filters->orderDateFrom);
        $dt = Util::createCommonDate($filters->orderDateTo);

        if ($stmt = $conn->prepare($sqlMeta->sql)) {
            $stmt->bind_param("ssssssiiddddssss",
                $filters->customerUid, $filters->customerUid,
                $filters->orderId, $filters->orderId,
                $likeVar = "%" . $filters->customerEmail . "%", $filters->customerEmail,
                $filters->orderStatusId, $filters->orderStatusId,
                $filters->sumFrom, $filters->sumFrom,
                $filters->sumTo, $filters->sumTo,
                $filters->orderDateFrom, $filters->orderDateFrom,
                $filters->orderDateTo, $filters->orderDateTo
            );
            $stmt->bind_result($orderId, $customerUid, $createDate, $orderDate, $sum, $currencyCode, $status,
                $confirmationUrl, $idempotenceKey, $statusCode, $customerEmail, $orderStatusName);
            $stmt->execute();
            $stmt->store_result();
            while($stmt->fetch() != false) {
                $order = Order::create(Util::bin2uuidString($orderId), $customerUid, $createDate, $orderDate, $sum, $currencyCode, $tariffName,
                    $status, $statusCode, $orderStatusName, $customerEmail, $confirmationUrl, Util::bin2uuidString($idempotenceKey));
                array_push($orders, $order);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_CUSTOMER_ORDERS->message);
        }

        $totalCount = 0;
        if ($stmt = $conn->prepare($this->orderCountSQL)) {
            $stmt->bind_param("ss", $filters->customerUid, $filters->customerUid);
            $stmt->bind_result($totalCount);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SEARCH_CALLS_COUNT->message);
        }

        $rawData = new stdClass();
        $rawData->orders = $orders;
        $rawData->totalPages = $totalCount % $size > 0 ? floor($totalCount / $size) + 1 : floor($totalCount / $size);

        return $rawData;
    }

    private function createGetOrdersSQL() {
        $result = new stdClass();
        $orderParam = 'order_date';
        $orderAsc = 'DESC';
        $sql = "select o.*, os.code, c.email, os.dsc from orders o join customer c on c.customer_uid = o.customer_uid 
                                        join order_status os on os.order_status_id = o.status where ";
        $sql .= " (o.customer_uid = ? or ? is null)";
        $sql .= " and (o.order_id = unhex(replace(?,'-','')) or ? is null)";
        $sql .= " and (c.email like ? or ? is null)";
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
