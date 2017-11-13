<?php

require_once dirname(__DIR__).'/../../../../vendor/ym/autoload.php';
require_once dirname(__DIR__).'/../AppConfig.php';
require_once dirname(__DIR__).'/../util/Repository.php';
require_once dirname(__DIR__).'/../util/ErrorRegistry.php';
require_once dirname(__DIR__).'/../util/Util.php';
require_once dirname(__DIR__).'/../repo/OrderRepo.php';
require_once dirname(__DIR__).'/../repo/OrderStatusRepo.php';
require_once dirname(__DIR__).'/../domain/Order.php';

use \YandexCheckout\Client;

class YKUtil {

    private $repository;
    private $errorRegistry;

    /**
     * SimpleRest constructor.
     */
    public function __construct()
    {
        $this->repository = new Repository();
        $this->errorRegistry = new ErrorRegistry();
    }

    public function makePayment($ord) {
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);

        $payment = [
            'amount' => $ord->sum,
            'payment_method_data' => [
                'type' => 'bank_card'
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => AppConfig::YK_RETURN_URL
            ]
        ];
        return $client->createPayment($payment, $ord->idempotenceKey);
    }

    public function checkPayment($paymentId) {
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);
        return $client->getPaymentInfo($paymentId);
    }

    public function capturePayment($order) {
        error_log("[pnservice]: " . " Capture order [" . $order->id . "]");
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);
        $captureRequest = [
            'amount' => [
                'value' => $order->sum,
                'currency' => $order->currencyCode
            ]
        ];
        $response = $client->capturePayment($captureRequest, $order->orderId, $order->idempotenceKey);
        return $this->checkCapture($order, $response);
    }

    public function cancelPayment($order) {
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);
        $response = $client->cancelPayment($order->orderId, $order->idempotenceKey);
        return $this->checkCancel($order, $response);
    }

    public function checkCancel($order, $payment) {
        if($payment['id'] != $order->orderId) { return false; }
        if($payment['status'] != 'canceled') { return false; }
        if($payment['paid'] != true) { return false; }
        if($payment->amount->value != $order->sum || $payment->amount->currency != $order->currencyCode ) { return false; }
        //charge или  refunded_amount
        return true;
    }

    public function checkCapture($order, $payment) {
        if($payment['id'] != $order->orderId) { return false; }
        if($payment['status'] != 'succeeded') { return false; }
        if($payment['paid'] != true) { return false; }
        if($payment->amount->value != $order->sum || $payment->amount->currency != $order->currencyCode ) { return false; }
        return true;
    }

    public function checkOrderWaiting($order, $payment) {
        if($payment['type'] != 'notification') { return false; }
        if($payment['event'] != 'payment.waiting_for_capture') { return false; }
        if($payment->object->status != 'waiting_for_capture') { return false; }
        if($payment->object->paid != true) { return false; }
        if($payment->object->amount->value != $order->sum || $payment->object->amount->currency != $order->currencyCode ) { return false; }
        if($payment->object->paid != true) { return false; }
        return true;
    }

    public function normalizeNotificationDateStr($dateObj) {
        $createdDate = str_replace('T', ' ', $dateObj);
        $createdDate = str_replace('Z', '', $createdDate);
        $dotPosition = strpos($createdDate, '.');
        $createdDate = $dotPosition != false ? substr($createdDate, 0, $dotPosition) : $createdDate;
        return $createdDate;
    }
}

//$e = new YKUtil();
//$e->checkPayment('21968c13-000f-500a-b000-0cef0417afa7');
