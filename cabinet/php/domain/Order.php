<?php
	
class Order {
	
	public $orderId;
	public $customerUid;
	public $createDate;
	public $orderDate;
	public $sum;
    public $currencyCode;
    public $statusId;
    public $statusCode;
    public $statusName;
    public $confirmationUrl;
    public $idempotenceKey;
    public $customerEmail;

    static function create($orderId, $customerUid, $createDate, $orderDate, $sum , $currencyCode, $statusId, $statusCode, $customerEmail, $confirmationUrl, $idempotenceKey)	{
        $obj = new Order();
        $obj->orderId = $orderId;
        $obj->customerUid = $customerUid;
        $obj->createDate = $createDate;
        $obj->orderDate = $orderDate;
        $obj->sum = $sum;
        $obj->currencyCode = $currencyCode;
        $obj->statusId  = $statusId;
        $obj->statusCode  = $statusCode;
        $obj->confirmationUrl  = $confirmationUrl;
        $obj->idempotenceKey  = $idempotenceKey;
        $obj->customerEmail = $customerEmail;
        return $obj;
	}

    static function createNew($customerUid, $sum, $currencyCode, $idempotenceKey)	{
        $obj = new Order();
        $obj->sum = $sum;
        $obj->createDate = Util::getCurrentDateFormatted();
        $obj->currencyCode = $currencyCode;
        $obj->customerUid = $customerUid;
        $obj->idempotenceKey = $idempotenceKey;
        return $obj;
    }

    static function createByReponse($ymResponse, $status, $idempotenceKey)	{
        $obj = new Order();
        $obj->orderId = $ymResponse['id'];
        $obj->orderDate = $ymResponse['createdAt']->date;
        $obj->sum = $ymResponse['amount']->_value;
        $obj->currencyCode = $ymResponse['amount']->_currency;
        $obj->statusId = $status->id;
        $obj->statusCode = $status->code;
        $obj->statusName = $status->dsc;
        $obj->confirmationUrl = $ymResponse['confirmation']->_confirmationUrl;
        $obj->idempotenceKey = $idempotenceKey;
        return $obj;
    }

    static function check($ord, $ordResponse) {
        if($ord->sum != $ordResponse->sum
            || $ord->currencyCode != $ordResponse->currencyCode) {
            return false;
        }
        return true;
    }

    static function fillFromReponse($order, $status, $id, $orderDate, $sum, $currency, $confirmationUrl) {
        $order->orderId = $id;
        $order->orderDate = $orderDate;
        $order->sum = $sum;
        $order->currencyCode = $currency;
        $order->statusId = $status->orderStatusId;
        $order->statusCode = $status->code;
        $order->statusName = $status->orderStatusName;
        $order->confirmationUrl = $confirmationUrl;
        return $order;
    }
}
