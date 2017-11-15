<?php

require_once("SimpleRest.php");
require_once(dirname(__DIR__).'/util/billing/YKUtil.php');
require_once(dirname(__DIR__).'/domain/Status.php');
error_log("YH2");
require_once(dirname(__DIR__).'/domain/Order.php');
error_log("YH3");

class YkHandler extends SimpleRest {

    private $orderByIdSQL = "select * from orders where order_id = unhex(replace(?,'-',''))";
    private $updateOrderSQL = "update orders set status = ? where order_id = unhex(replace(?,'-',''))";
    private $insertCashOperationSQL = "insert into balance_operation(customer_uid, oper_date, sum, dsc, order_id)
                      values(?,?,?,?,?, unhex(replace(?,'-','')) )";

    function init(){
        $dbname = "host1563047";
        $servername = "localhost";
        $username = "host1563047";
        $password = "lsGer6ham";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->autocommit(FALSE);
        return $conn;
    }

    public function process($paymentObj) {
        error_log("[pnservice]: " . " Processing order " . $paymentObj->object->id . " started ");
        $conn = $this->init();
        $result = new stdClass();
        try {
            error_log("[pnservice]: " . " Processing order [" . $paymentObj['object']->id . "]");
            $yk = new YKUtil();
            $order = $this->getOrderById($conn, $paymentObj['object']->id);

            if ($order->currencyCode != AppConfig::DEFAULT_CURRENCY) {
                $this->handleResult("Валюта не поддерживается");
                return;
            }


            if ($yk->checkOrderWaiting($order, $paymentObj) == false) {
                $this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE_WRONG_NOTIFICATION);
                throw new Exception("[Сервис обработки платежных уведомлений]: Получено некорректное платежное уведомление");
            }
            error_log("[pnservice]: " . " Order [" . $paymentObj['object']->id . "] is in waiting status. Updating.");
            $this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE);

            if ($yk->capturePayment($order)) {
                error_log("[pnservice]: " . " Capture order [" . $order->id . "] Failed. Updating status.");
                $this->updateOrderStatus($conn, $order->orderId, Status::CAPTURE_FAILED);
            } else {
                error_log("[pnservice]: " . " Capture order [" . $order->id . "] OK. Updating status.");
                $msg = "Пополнение баланса клиента";
                $cashOperation = new CashOper(null, $order->customerUid, Util::getCurrentServerDateFormatted(), $order->sum, $msg, null);
                $this->refill($conn, $cashOperation);
                $this->updateOrderStatus($conn, $order->orderId, Status::SUCCEEDED);
            }
        }
        catch(Exception $ex) {
            $conn->rollback();
            $conn->close();
            throw new Exception($ex->getMessage());
        }

        $conn->commit();
        $conn->close();

        $this->handleResult($result);
    }

    private function getOrderById($conn, $id) {
        $order = null;

        $orderId = null;
        $customerUid = null;
        $createDate = null;
        $orderDate = null;
        $sum = null;
        $currencyCode = null;
        $statusId = null;
        $statusCode = null;
        $confirmationUrl = null;
        $idempotenceKey = null;

        if ($stmt = $conn->prepare($this->orderByIdSQL)) {
            $stmt->bind_param("s", $id);

            $stmt->bind_result($orderId, $customerUid, $createDate, $orderDate, $sum , $currencyCode, $statusId, $confirmationUrl, $idempotenceKey);
            $stmt->execute();

            if($stmt->fetch() != false) {
                $order = Order::create($orderId, $customerUid, $createDate, $orderDate, $sum , $currencyCode, $statusId, null, null, $confirmationUrl, $idempotenceKey);
            }
            $stmt->close();
        }
        else {
            throw new Exception( "[Сервис обработки платежных уведомлений]: Ошибка получения данных платежа");
        }

        if($order == null) {
            throw new Exception( "[Сервис обработки платежных уведомлений]: Платеж с указанным ID отсутствует");
        }

        return $order;
    }

    private function updateOrderStatus($conn, $orderId, $statusId)
    {
        if ($stmt = $conn->prepare($this->updateOrderSQL)) {
            $stmt->bind_param("is", $statusId, $orderId);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( "[Сервис обработки платежных уведомлений]: Ошибка обновления статуса платежа");
        }
        return true;
    }

    public function refill($conn, $operation)
    {
        if ($stmt = $conn->prepare($this->insertCashOperationSQL)) {
            $stmt->bind_param("ssdsss",
                $operation->customerUid,
                $operation->operDate,
                $operation->sum,
                $operation->dsc,
                $operation->orderId,
                $operation->orderId
            );
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_INSERT_CASH_OPERATION->message);
        }

        return true;
    }

//    public function processMain($requestObj) {
//        $yk = YKUtil();
//        $paymentObj = array('request' => $requestObj);
//        $repo = OrderRepo::getInstance();
//        $order = $repo->getOrderById($paymentObj['object']->id);
//        if($yk->checkOrderWaiting($order, $paymentObj) == false) {
//            throw new Exception( $this->getErrorRegistry()->USER_ERR_WRONG_PAYMENT_NOTIFICATION->message);
//        }
//        $repo->updateOrderStatus($order->orderId, Status::WAITING_FOR_CAPTURE);
//
//        if($yk->capturePayment($order)) {
//            $repo->updateOrderStatus($order->orderId, Status::CAPTURE_FAILED);
//        }
//
//        $result = new stdClass();
//        $this->handleResult($result);
//    }

}