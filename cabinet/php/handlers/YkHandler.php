<?php

require_once("SimpleRest.php");
require_once("../util/billing/YKUtil.php");
require_once("../domain/Status.php");
require_once("../domain/Order.php");

class YkHandler extends SimpleRest {

    public $isTestInstance = true;

    private $orderByIdSQL = 'select * from orders where order_id = unhex(replace(?,\'-\',\'\'))';
    private $updateOrderSQL = 'update orders set status = ? where order_id = unhex(replace(?,\'-\',\'\'))';

    function init(){
        $dbname = $this->isTestInstance ? "host1563047" : "host1563047_main";
        $servername = $this->isTestInstance ? "localhost" : "localhost";
        $username = $this->isTestInstance ? "host1563047" : "host1563047_main";
        $password = $this->isTestInstance ? "lsGer6ham" : "JHDpUJjE";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        $conn->autocommit(FALSE);
        return $conn;
    }

    public function process($requestObj) {
        $yk = YKUtil();
        $paymentObj = array('request' => $requestObj);
        $order = $this->getOrderById($paymentObj['object']->id);
        if($yk->checkOrderWaiting($order, $paymentObj) == false) {
            throw new Exception( "[Сервис обработки платежных уведомлений]: Получено некорректное платежное уведомление");
        }

        $this->updateOrderStatus($order->orderId, Status::WAITING_FOR_CAPTURE);

        if($yk->capturePayment($order)) {
            $this->updateOrderStatus($order->orderId, Status::CAPTURE_FAILED);
        }
        else {
            $this->updateOrderStatus($order->orderId, Status::SUCCEEDED);
        }

        $result = new stdClass();
        $this->handleResult($result);
    }

    private function getOrderById($id) {
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

        $conn = $this->init();

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

    private function execute($orderId, $statusId)
    {
        $conn = $this->init();
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