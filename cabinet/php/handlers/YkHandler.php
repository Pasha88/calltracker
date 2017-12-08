<?php

require_once(dirname(__DIR__)."/handlers/SimpleRest.php");
require_once(dirname(__DIR__).'/util/billing/YKUtil.php');
require_once(dirname(__DIR__).'/domain/Status.php');
require_once(dirname(__DIR__).'/domain/Order.php');
require_once(dirname(__DIR__).'/domain/CashOper.php');

class YkHandler extends SimpleRest {

    private $orderByIdSQL = "select order_id, customer_uid, create_date, order_date, sum, currency_code, status, confirmation_url, idempotence_key from orders where order_id = unhex(replace(?,'-',''))";
    private $updateOrderSQL = "update orders set status = ? where order_id = unhex(replace(?,'-',''))";
    private $updateIdempotenceKeySQL = "update orders set idempotence_key = unhex(replace(?,'-','')) where order_id = unhex(replace(?,'-',''))";
    private $insertCashOperationSQL = "insert into balance_operation(customer_uid, oper_date, sum, dsc, order_id)
                      values(?,?,?,?, unhex(replace(?,'-','')) )";

    private $customerBalanceSQL = "select balance from customer where customer_uid = ?";
    private $updateBalanceSQL = "update customer set balance = ? where customer_uid = ?";

    private $ordersForProcessSQL = "select order_id, customer_uid, create_date, order_date, sum, currency_code, status, confirmation_url, idempotence_key 
                                    from orders where status in (-2, -1, 1, 2) LIMIT 1";


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

    public function process($conn, $paymentInput) {
        $this->processSingle($conn, $paymentInput);
        $this->handleResult("OK");
    }

    public function processSingle($conn, $paymentInput) {
error_log("stage01");
        $yk = new YKUtil();
        if($yk->checkNotification($paymentInput)) {
            $paymentObj = $paymentInput->object;
error_log("stage02");
        }
        else {
            $paymentObj = $paymentInput;
error_log("stage03");
        }

        try {
            $conn = isset($conn) == false ? $this->init() : $conn;
            $result = new stdClass();
            $order = $this->getOrderById($conn, $paymentObj->id);
error_log("stage04 ORDER [" . Util::bin2uuidString($order->orderId) . "]");
            if ($order->currencyCode != AppConfig::DEFAULT_CURRENCY) {
                $msg = "[Payment processing service]: Валюта не поддерживается для платежа [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
                $this->handleResult($msg);
                return;
            }

            if ($yk->checkNotification($paymentInput) && $yk->checkOrderWaiting($order, $paymentObj) == false) {
error_log("stage05");
                $this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE_WRONG_NOTIFICATION);
                $conn->commit();
                $conn->close();
                $msg = "[Payment processing service]: Получено некорректное платежное уведомление для платежа [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
                throw new Exception($msg);
            }
error_log("stage06");
            switch (Status::byCode($paymentObj->status)) {
                case Status::PENDING:
error_log("stagePENDING");
                    break;
                case Status::WAITING_FOR_CAPTURE:
error_log("stageWAITINGFORCAPTURE");
                    $this->processWaitingForCapture($conn, $order);
                    break;
                case Status::SUCCEEDED:
error_log("stageSUCCEEDED");
                    $this->processSucceeded($conn, $order);
                    break;
                case Status::CANCELED:
error_log("stageCANCELED");
                    $this->processCanceled($conn, $order);
                    break;
            }
            $conn->close();
        }
        catch(Exception $ex) {
            if(isset($conn)) {
                $conn->rollback();
                $conn->close();
            }
            $this->handleError($ex->getMessage());
            throw new Exception($ex->getMessage());
        }
    }

    public function processScheduled() {
        $conn = $this->init();
        $yk = new YKUtil();
        $orders = $this->getOrdersForProcess($conn);

        foreach($orders as $order) {
            try {
                $paymentResponse = $yk->checkPayment(strtolower($order->orderId));
                $this->processSingle($conn, $paymentResponse);
            }
            catch(Exception $ex) {
                $msg = "[Payment processing service]: Не удалось обработать платеж [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
            }
        }
        $this->handleResult("OK");
    }

    private function processPending() {
    }

    private function processWaitingForCapture($conn, $order) {
        $yk = new YKUtil();
        if($this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE)) {
            $conn->commit();
        }
        else {
            $msg = "[Payment processing service]: Не удается сохранить статус WAITING_FOR_CAPTURE для платежа " . Util::bin2uuidString($order->orderId);
            error_log($msg);
            throw new Exception($msg);
        }

        $idempotenceKey = Util::uuid();
        if($this->updateOrderIdempotenceKey($conn, $order->orderId, $idempotenceKey) == true) {
            $conn->commit();
        }
        else {
            $msg = "[Payment processing service]: Не удается сохранить idempotence key для подтверждения платежа [" . Util::bin2uuidString($order->orderId) . "]";
            error_log($msg);
            throw new Exception($msg);
        }
        if ($yk->capturePayment($order, $idempotenceKey) == false) {
            if($this->updateOrderStatus($conn, $order->orderId, Status::CAPTURE_FAILED) == true) {
                $conn->commit();
            }
            else {
                $msg = "[Payment processing service]: Не удается сохранить статус CAPTURE_FAILED для платежа " . Util::bin2uuidString($order->orderId);
                error_log($msg);
                throw new Exception($msg);
            }
        } else {
            $this->processSucceeded($conn, $order);
        }
    }

    private function processSucceeded($conn, $order) {
        $infomsg = "Пополнение баланса клиента";
        $cashOperation = new CashOper(null, $order->customerUid, Util::getCurrentServerDateFormatted(), $order->sum, $infomsg, Util::bin2uuidString($order->orderId));

        if($this->refill($conn, $cashOperation)
            && $this->updateBalance($conn, $cashOperation)
            && $this->updateOrderStatus($conn, $order->orderId, Status::SUCCEEDED)) {
            $conn->commit();
        }
        else {
            $msg = "[Payment processing service]: Не удается сохранить статус SUCCEEDED для платежа " . Util::bin2uuidString($order->orderId);
            error_log($msg);
            throw new Exception($msg);
        }
    }

    private function processCanceled($conn, $order) {
        $this->updateOrderStatus($conn, $order->orderId, Status::CANCELED);
        $conn->commit();
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
                $order = Order::create($orderId, $customerUid, $createDate, $orderDate, $sum , $currencyCode, $statusId, null, null, $confirmationUrl, $idempotenceKey, null);
            }
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка получения данных платежа " . Util::bin2uuidString($id);
            error_log($msg);
            throw new Exception($msg);
        }

        if(!isset($order) || !isset($order->orderId)) {
            $msg = "[Payment processing service]: Платеж с указанным ID отсутствует " . Util::bin2uuidString($id);
            error_log($msg);
            throw new Exception($msg);
        }

        return $order;
    }

    private function updateOrderStatus($conn, $orderId, $statusId)
    {
        if ($stmt = $conn->prepare($this->updateOrderSQL)) {
            $stmt->bind_param("is", $statusId, strtolower(Util::bin2uuidString($orderId)));
            $stmt->execute();
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка обновления статуса платежа " . Util::bin2uuidString($orderId);
            error_log($msg);
            throw new Exception($msg);
        }
        return true;
    }

    public function refill($conn, $operation)
    {
        if ($stmt = $conn->prepare($this->insertCashOperationSQL)) {
            $stmt->bind_param("ssdss",
                $operation->customerUid,
                $operation->operDate,
                $operation->sum,
                $operation->dsc,
                $operation->orderId
            );
            $stmt->execute();
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка создания балансовой операции пополнения " . Util::bin2uuidString($operation->orderId);
            error_log($msg);
            throw new Exception($msg);
        }

        return true;
    }

    private function updateOrderIdempotenceKey($conn, $orderId, $idempotenceKey)
    {
        if ($stmt = $conn->prepare($this->updateIdempotenceKeySQL)) {
            $stmt->bind_param("ss", $idempotenceKey, strtolower(Util::bin2uuidString($orderId)));
            $stmt->execute();
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка обновления статуса платежа " . Util::bin2uuidString($orderId);
            error_log($msg);
            throw new Exception( $msg);
        }
        return true;
    }

    private function updateBalance($conn, $cashOperation) {

        $currentBalance = null;
        if ($stmt = $conn->prepare($this->customerBalanceSQL)) {
            $stmt->bind_param("s", $cashOperation->customerUid);
            $stmt->bind_result($currentBalance);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->fetch() == false) {
                $msg = "[Payment processing service]: Ошибка получения баланса клиента [" . $cashOperation->customerUid . "] для обновления";
                error_log($msg);
                throw new Exception( $msg);
            }
        }
        else {
            $msg = "[Payment processing service]: Ошибка при получении баланса клиента [" . $cashOperation->customerUid . "] для обновления";
            error_log($msg);
            throw new Exception( $msg);
        }
        $currentBalance += $cashOperation->sum;
        if ($stmt = $conn->prepare($this->updateBalanceSQL)) {
            $stmt->bind_param("ds", $currentBalance, $cashOperation->customerUid);
            $stmt->execute();

            if($stmt->affected_rows != 1) {
                $msg = "[Payment processing service]: Не удалось обновить баланс клиента [" . $cashOperation->customerUid . "]";
                error_log($msg);
                throw new Exception( $msg);
            }
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка при обновлении баланса клиента [" . $cashOperation->customerUid . "]";
            error_log($msg);
            throw new Exception( $msg);
        }

        return true;
    }

    private function getOrdersForProcess($conn) {
        $orders = array();
        $orderId = null;
        $customerUid = null;
        $createDate = null;
        $orderDate = null;
        $sum = null;
        $currencyCode = null;
        $status = null;
        $confirmationUrl = null;
        $idempotenceKey = null;

        if ($stmt = $conn->prepare($this->ordersForProcessSQL)) {
            $stmt->bind_result($orderId, $customerUid, $createDate, $orderDate, $sum, $currencyCode, $status,
                $confirmationUrl, $idempotenceKey);
            $stmt->execute();
            $stmt->store_result();
            while($stmt->fetch() != false) {
                $order = Order::create(Util::bin2uuidString($orderId), $customerUid, $createDate, $orderDate, $sum, $currencyCode,
                    $status, null, null, $confirmationUrl, Util::bin2uuidString($idempotenceKey), null);
                array_push($orders, $order);
            }
            $stmt->close();
        }
        else {
            $msg = "[Payment processing service]: Ошибка при получении платежей для обработки";
            error_log($msg);
            throw new Exception( $msg);
        }
        return $orders;
    }
}
