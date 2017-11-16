<?php

class CustomerTariff
{
    public $customerId;
    public $tariffId;
    public $setDate;

    static function create($customerId, $tariffId, $setDate)	{
        $obj = new CustomerTariff();
        $obj->customerId = $customerId;
        $obj->tariffId = $tariffId;
        $obj->setDate = $setDate;
        return $obj;
    }

}