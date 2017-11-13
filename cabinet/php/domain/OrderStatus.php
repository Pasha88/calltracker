<?php

class OrderStatus {

    public $orderStatusId;
    public $code;
    public $orderStatusName;

    function __construct($orderStatusId, $code, $orderStatusName)	{
        $this->orderStatusId = $orderStatusId;
        $this->code = $code;
        $this->orderStatusName = $orderStatusName;
    }

}
