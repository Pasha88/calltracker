<?php

require_once("SimpleRest.php");
require_once(dirname(__DIR__).'/util/billing/YKUtil.php');
require_once(dirname(__DIR__).'/domain/Status.php');
require_once(dirname(__DIR__).'/domain/Order.php');
require_once(dirname(__DIR__).'/domain/CashOper.php');

class YkHandler extends SimpleRest {

    private $orderByIdSQL = "select * from orders where order_id = unhex(replace(?,'-',''))";
    private $updateOrderSQL = "update orders set status = ? where order_id = unhex(replace(?,'-',''))";
    private $updateIdempotenceKeySQL = "update orders set idempotence_key = unhex(replace(?,'-','')) where order_id = unhex(replace(?,'-',''))";
    private $insertCashOperationSQL = "insert into balance_operation(customer_uid, oper_date, sum, dsc, order_id)
                      values(?,?,?,?, unhex(replace(?,'-','')) )";

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
        try {
            $conn = $this->init();
            $result = new stdClass();
            $yk = new YKUtil();
            $order = $this->getOrderById($conn, $paymentObj->object->id);

            if ($order->currencyCode != AppConfig::DEFAULT_CURRENCY) {
                $msg = "[Сервис обработки платежных уведомлений]: Валюта не поддерживается для платежа [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
                $this->handleResult($msg);
                return;
            }

            if ($yk->checkOrderWaiting($order, $paymentObj) == false) {
                $this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE_WRONG_NOTIFICATION);
                $conn->commit();
                $conn->close();
                $msg = "[Сервис обработки платежных уведомлений]: Получено некорректное платежное уведомление для платежа [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
                throw new Exception($msg);
            }

            $this->updateOrderStatus($conn, $order->orderId, Status::WAITING_FOR_CAPTURE);
            $conn->commit();

            $idempotenceKey = Util::uuid();
            if($this->updateOrderIdempotenceKey($conn, $order->orderId, $idempotenceKey) == true) {
                $conn->commit();
            }
            else {
                $msg = "[Сервис обработки платежных уведомлений]: Не удается сохранить idempotence key для подтверждения платежа [" . Util::bin2uuidString($order->orderId) . "]";
                error_log($msg);
                throw new Exception($msg);
            }

            if ($yk->capturePayment($order, $idempotenceKey)) {
                if($this->updateOrderStatus($conn, $order->orderId, Status::CAPTURE_FAILED) == true) {
                    $conn->commit();
                }
                else {
                    $msg = "[Сервис обработки платежных уведомлений]: Не удается сохранить статус CAPTURE_FAILED для платежа " . Util::bin2uuidString($order->orderId);
                    error_log($msg);
                    throw new Exception($msg);
                }
            } else {
                $infomsg = "Пополнение баланса клиента";
                $cashOperation = new CashOper(null, $order->customerUid, Util::getCurrentServerDateFormatted(), $order->sum, $infomsg, Util::bin2uuidString($order->orderId));
                $this->refill($conn, $cashOperation);
                if($this->updateOrderStatus($conn, $order->orderId, Status::SUCCEEDED)) {
                    $conn->commit();
                }
                else {
                    $msg = "[Сервис обработки платежных уведомлений]: Не удается сохранить статус SUCCEEDED для платежа " . Util::bin2uuidString($order->orderId);
                    error_log($msg);
                    throw new Exception($msg);
                }
            }
        }
        catch(Exception $ex) {
            if(isset($conn)) {
                $conn->rollback();
                $conn->close();
            }
            $this->handleError($ex->getMessage());
            throw new Exception($ex->getMessage());
        }
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
                $order = Order::create($orderId, $customerUid, $createDate, $orderDate, $sum , $currencyCode, $statusId, null, null, $confirmationUrl, $idempotenceKey, null);
            }
            $stmt->close();
        }
        else {
            $msg = "[Сервис обработки платежных уведомлений]: Ошибка получения данных платежа " . Util::bin2uuidString($order->orderId);
            error_log($msg);
            throw new Exception($msg);
        }

        if(!isset($order) || !isset($order->orderId)) {
            $msg = "[Сервис обработки платежных уведомлений]: Платеж с указанным ID отсутствует " . Util::bin2uuidString($order->orderId);
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
            $msg = "[Сервис обработки платежных уведомлений]: Ошибка обновления статуса платежа " . Util::bin2uuidString($orderId);
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
            $msg = "[Сервис обработки платежных уведомлений]: Ошибка создания балансовой операции пополнения " . Util::bin2uuidString($operation->orderId);
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
            $msg = "[Сервис обработки платежных уведомлений]: Ошибка обновления статуса платежа " . Util::bin2uuidString($orderId);
            error_log($msg);
            throw new Exception( "[Сервис обработки платежных уведомлений]: Ошибка обновления статуса платежа");
        }
        return true;
    }

}