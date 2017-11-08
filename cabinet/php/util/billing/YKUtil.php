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

    public function makePayment($customerUid, $sum, $currencyCode) {
        $idempotenceKey = Util::uuid();

        $ord = Order::createNew($customerUid, $sum, $currencyCode, $idempotenceKey);

        $client = new Client();
        $client->setAuth(AppConfig::SHOP_ID, AppConfig::YKKEY);

        $payment = [
            'amount' => $ord->sum,
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => AppConfig::YK_RETURN_URL
            ]
        ];

        $paymentResponse = $client->createPayment($payment, $idempotenceKey);
        $status = OrderStatusRepo::getInstance()->byCode(strtoupper($paymentResponse['status']));
        $ord = Order::fillFromReponse($ord, $status, $paymentResponse['id'], Util::formatDate($paymentResponse['createdAt']),
            $paymentResponse['amount']->_value, $paymentResponse['amount']->_currency, $paymentResponse['confirmation']->_confirmationUrl);
        OrderRepo::getInstance()->insertOrder($ord);
        return $ord->confirmationUrl;
    }
}
