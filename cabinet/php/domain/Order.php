<?php
	
class Order {
	
	public $orderId;
	public $customerUid;
	public $orderDate;
	public $sum;

    function __construct($orderId, $customerUid, $orderDate, $sum)	{
        $this->orderId = $orderId;
        $this->customerUid = $customerUid;
        $this->orderDate = $orderDate;
        $this->sum = $sum;
	}

}
