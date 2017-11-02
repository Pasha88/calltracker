<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__)."/domain/Call.php");
require_once("customer/FindCustomerCommandByUid.php");

class SearchCallsCommand extends Command {

    private $callCountSQL = 'select count(co.call_object_id) as cnt  from call_object co, number_pool pn where pn.id = co.number_id and pn.customer_id = ?';
    private $searchCallSQL = 'select co.call_object_id, co.client_id, co.call_date_time, co.type_id, co.description, co.customer_id, pn.phone_number, pn.id 
                              from call_object co left join number_pool pn on co.number_id = pn.id where co.customer_id = ?';
    private $getLastCallId = 'select max(call_object_id) from call_object';
    private $yesterdayLoadCalls = 'SELECT count(1) FROM call_object where datediff(now(), call_date_time) = 1 and ya_upload = 1';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {

        $sql = $this->searchCallSQL;
        $orderParam = "call_date_time";
        $orderAsc = false ? "ASC" : "DESC"; //true заменить переменной

        $page = $this->args->page;
        $size = $this->args->size;

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args->customerUid ) );
        $customer = $c->execute($conn);

        $customerId = $customer->customerId;

        if(isset($page)) {
            if(!isset($size)) {
                $size = 25;
            }
            $sql .= " ORDER BY " . $orderParam . " " . $orderAsc;
            $sql .= " LIMIT " . $page*$size . "," . $size;
        }

        if ($stmt = $conn->prepare($sql)) {

            $stmt->bind_param("i", $customerId);

            $row = array();
            $stmt->bind_result($row['call_object_id'], $row['client_id'], $row['call_date_time'], $row['type_id'], $row['description'], $row['customer_id'], $row['number'], $row['id']);
            $stmt->execute();

            $i=0;
            $result = array();
            while ($stmt->fetch())
            {
                $result[$i] = new Call($row);
                $result[$i]->setClientTimeOffset($customer->timeZone);
                $i++;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SEARCH_CALLS->message);
        }

        $totalCount = 0;
        if ($stmt = $conn->prepare($this->callCountSQL)) {
            $stmt->bind_param("i", $customerId);
			$stmt->bind_result($totalCount);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SEARCH_CALLS_COUNT->message);
        }

        $res = null;
        if ($stmt = $conn->prepare($this->getLastCallId)) {
            $stmt->bind_result($res);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_GET_LAST_CALL_ID->message);
        }

        $yesterdayLoadCalls = null;
        if ($stmt = $conn->prepare($this->yesterdayLoadCalls)) {
            $stmt->bind_result($res);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_GET_YESTERDAY_YA_LOAD_CALLS->message);
        }

        $rawData = new stdClass();
        $rawData->data = $result;
        $rawData->totalPages = $totalCount % $size > 0 ? floor($totalCount / $size) + 1 : floor($totalCount / $size);
        $rawData->lastCallId = $res;
        $rawData->yesterdayCallsYaSent = $yesterdayLoadCalls;

        if(!isset($customer->yaIdAuth) || $customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID || !isset($customer->yaId)) {
            $rawData->yaIdAuthValid = false;
        }
        else {
            $rawData->yaIdAuthValid = true;
        }
        return $rawData;
    }

}