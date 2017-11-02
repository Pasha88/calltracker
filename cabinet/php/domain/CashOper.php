<?php
	
class CashOper {
	
	public $operId;
	public $customerUid;
	public $operDate;
	public $sum;
    public $dsc;
    public $orderId;

    function __construct($operId, $customerUid, $operDate, $sum, $dsc, $orderId)	{
        $this->operId = $operId;
        $this->customerUid = $customerUid;
        $this->operDate = $operDate;
        $this->sum = $sum;
        $this->dsc = $dsc;
        $this->orderId = $orderId;
	}

}
