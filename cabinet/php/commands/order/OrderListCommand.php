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
        $confirmationUrl = null;
        $idempotenceKey = null;

        $filters = $this->args['filters'];
        $page = $filters->page;
        $size = $filters->size;

        $sqlMeta = $this->createGetOrdersSQL($page, $size);

        $df = Util::createCommonDate($filters->orderDateFrom);
        $dt = Util::createCommonDate($filters->orderDateTo);

        $filters->customerUid = isset($filters->customerUid) ? $filters->customerUid : null;
        $filters->orderId = isset($filters->orderId) ? $filters->orderId : null;
        $filters->customerEmail = isset($filters->customerEmail) ? $filters->customerEmail : null;
        $filters->orderStatusId = isset($filters->orderStatusId) ? $filters->orderStatusId : null;
        $filters->sumFrom = isset($filters->sumFrom) ? $filters->sumFrom : null;
        $filters->sumTo = isset($filters->sumTo) ? $filters->sumTo : null;
        $filters->orderDateFrom = isset($filters->orderDateFrom) ? $filters->orderDateFrom:  null;
        $filters->orderDateTo = isset($filters->orderDateTo) ? $filters->orderDateTo : null;

        $emailLike = "%" . $filters->customerEmail . "%";

        if ($stmt = $conn->prepare($sqlMeta->sql)) {
            $stmt->bind_param("ssssssiiddddssss",
                $filters->customerUid, $filters->customerUid,
                $filters->orderId, $filters->orderId,
                $emailLike, $filters->customerEmail,
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
                $order = Order::create(Util::bin2uuidString($orderId), $customerUid, $createDate, $orderDate, $sum, $currencyCode,
                    $status, $statusCode, $customerEmail, $confirmationUrl, Util::bin2uuidString($idempotenceKey));
                array_push($orders, $order);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_CUSTOMER_ORDERS->message);
        }

        $totalCount = 0;
        if ($stmt = $conn->prepare($sqlMeta->sqlCount)) {
            $stmt->bind_param("ssssssiiddddssss",
                $filters->customerUid, $filters->customerUid,
                $filters->orderId, $filters->orderId,
                $emailLike, $filters->customerEmail,
                $filters->orderStatusId, $filters->orderStatusId,
                $filters->sumFrom, $filters->sumFrom,
                $filters->sumTo, $filters->sumTo,
                $filters->orderDateFrom, $filters->orderDateFrom,
                $filters->orderDateTo, $filters->orderDateTo
            );
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

    private function createGetOrdersSQL($page, $size) {
        $result = new stdClass();
        $orderParam = 'order_date';
        $orderAsc = 'DESC';

        $sqlCount = "select count(o.order_id) as cnt  from orders o join customer c on c.customer_uid = o.customer_uid join order_status os on os.order_status_id = o.status where ";

        $sqlSearch = "select o.*, os.code, c.email, os.dsc from orders o join customer c on c.customer_uid = o.customer_uid 
                                        join order_status os on os.order_status_id = o.status where ";

        $sqlOrder = "";

        $sql = " (o.customer_uid = ? or ? is null)";
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
            $sqlOrder .= " ORDER BY " . $orderParam . " " . $orderAsc;
            $sqlOrder .= " LIMIT " . $page*$size . "," . $size;
        }

        $result->sql = $sqlSearch . $sql . $sqlOrder;
        $result->sqlCount = $sqlCount . $sql;
        $result->bindString = $bindString;
        return  $result;
    }
}
