<?php
	
class Tariff {
	
	public $tariffId;
	public $tariffName;
	public $maxPhoneNumber;
	public $rate;
    public $isDeleted;

    function __construct($tariffId, $tariffName, $maxPhoneNumber, $rate, $isDeleted) {
        $this->tariffId = $tariffId;
        $this->tariffName = $tariffName;
        $this->maxPhoneNumber = $maxPhoneNumber;
        $this->rate = $rate;
        $this->isDeleted = $isDeleted;
	}

}
