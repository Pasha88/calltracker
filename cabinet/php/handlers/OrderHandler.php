<?php

require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/domain/Order.php");
require_once(dirname(__DIR__)."/AppConfig.php");
require_once(dirname(__DIR__)."/util/Util.php");
require_once(dirname(__DIR__)."/util/billing/YKUtil.php");
require_once(dirname(__DIR__)."/repo/OrderStatusRepo.php");
require_once(dirname(__DIR__)."/repo/OrderRepo.php");

class OrderHandler extends SimpleRest {

    public function makePayment($requestObj) {
        $ykUtil = new YKUtil();
        $ord = Order::createNew($requestObj->customerUid, $requestObj->sum, AppConfig::DEFAULT_CURRENCY, Util::uuid());
        $paymentResponse = $ykUtil->makePayment($ord);

        $status = OrderStatusRepo::getInstance()->byCode(strtoupper($paymentResponse['status']));
        $ord = Order::fillFromReponse($ord, $status, $paymentResponse['id'], Util::formatDate($paymentResponse['createdAt']->setTimeZone(Util::getServerTz())),
            $paymentResponse['amount']->_value, $paymentResponse['amount']->_currency, $paymentResponse['confirmation']->_confirmationUrl);
        OrderRepo::getInstance()->insertOrder($ord);
        return $ord->confirmationUrl;
    }

    public function checkPayment($paymentId) {
        $ykUtil = new YKUtil();
        $response = $ykUtil->checkPayment($paymentId);
        $status = OrderStatusRepo::getInstance()->byCode(strtoupper($response['status']));
    }

    public function getOrders($requestObj) {
        $repo = OrderRepo::getInstance();
        return $repo->orderList($requestObj->filters);
    }

    public function getAllStatuses() {
        $repo = OrderStatusRepo::getInstance();
        return $repo->getAll();
    }

}