<?php
require_once (dirname(__DIR__).'/../../../../vendor/ym/autoload.php');
require_once (dirname(__DIR__).'/../AppConfig.php');
require_once (dirname(__DIR__).'/Repository.php');
require_once (dirname(__DIR__).'/Util.php');

use \YandexCheckout\Client;

class YKUtil {

    private $repository;

    /**
     * SimpleRest constructor.
     */
    public function __construct()
    {
        $this->repository = new Repository();
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

    public function capturePayment($order, $idempotenceKey) {
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);
        $captureRequest = [
            'amount' => [
                'value' => $order->sum,
                'currency' => $order->currencyCode
            ]
        ];
        $response = $client->capturePayment($captureRequest, strtolower(Util::bin2uuidString($order->orderId)), $idempotenceKey);
        print(json_encode($response));
        return $this->checkCapture($order, $response);
    }

    public function cancelPayment($order) {
        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);
        $response = $client->cancelPayment( strtolower(Util::bin2uuidString($order->orderId)), strtolower(Util::bin2uuidString($order->idempotenceKey)));
        return $this->checkCancel($order, $response);
    }

    public function checkCancel($order, $payment) {
        if($payment->id != $order->orderId) { return false; }
        if($payment->status != 'canceled') { return false; }
        if($payment->paid != true) { return false; }
        if($payment->amount->value != $order->sum || $payment->amount->currency != $order->currencyCode ) { return false; }
        //charge или  refunded_amount
        return true;
    }

    public function checkCapture($order, $payment) {
        if($payment->id != strtolower(Util::bin2uuidString($order->orderId))) { return false; }
        if($payment->status != 'succeeded') { return false; }
        if($payment->paid != true) { return false; }
        if($payment->amount->value != $order->sum || $payment->amount->currency != $order->currencyCode ) { return false; }
        return true;
    }

    public function checkOrderWaiting($order, $payment) {
        if($payment->status != 'waiting_for_capture') { return false; }
        if($payment->paid != true && $payment->paid != 1) { return false; }
        if($payment->amount->value != $order->sum || $payment->amount->currency != $order->currencyCode ) { return false; }
        return true;
    }

    public function checkNotification($payment) {
        if($payment->type != 'notification') { return false; }
        if($payment->event != 'payment.waiting_for_capture') { return false; }
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

