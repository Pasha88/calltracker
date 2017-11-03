<?php
	
class Order {
	
	public $orderId;
	public $customerUid;
	public $orderDate;
	public $sum;
    public $tariffId;
    public $status;
    public $statusCode;
    public $customerEmail;
    public $orderStatusName;
    public $tariffName;

    function __construct($orderId, $customerUid, $orderDate, $sum, $tariffId, $status, $statusCode, $customerEmail, $orderStatusName, $tariffName)	{
        $this->orderId = $orderId;
        $this->customerUid = $customerUid;
        $this->orderDate = $orderDate;
        $this->sum = $sum;
        $this->tariffId = $tariffId;
        $this->status = $status;
        $this->statusCode = $statusCode;
        $this->customerEmail = $customerEmail;
        $this->orderStatusName = $orderStatusName;
        $this->tariffName = $tariffName;
	}

}
