<?php

require_once("SimpleRest.php");
require_once("../util/billing/YKUtil.php");

class YkHandler extends SimpleRest {

    public function __construct()
    {
        $this->errorRegistry = new ErrorRegistry();
    }

    public function process($requestObj) {
        $yk = YKUtil();
        $paymentObj = array('request' => $requestObj);
        $repo = OrderRepo::getInstance();
        $order = $repo->getOrderById($paymentObj['object']->id);
        if($yk->checkOrderWaiting($order, $paymentObj) == false) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_WRONG_PAYMENT_NOTIFICATION->message);
        }
        $repo->updateOrderStatus($order->orderId, Status::WAITING_FOR_CAPTURE);

        if($yk->capturePayment($order)) {
            $repo->updateOrderStatus($order->orderId, Status::CAPTURE_FAILED);
        }
        else {
            $repo->updateOrderStatus($order->orderId, Status::SUCCEEDED);
        }

        $result = new stdClass();
        $this->handleResult($result);
    }

}