<?php
	
class Tariff {
	
	public $tariffId;
	public $tariffName;
	public $maxPhoneNumber;
	public $rate;

    function __construct($tariffId, $tariffName, $maxPhoneNumber, $rate) {
        $this->tariffId = $tariffId;
        $this->tariffName = $tariffName;
        $this->maxPhoneNumber = $maxPhoneNumber;
        $this->rate = $rate;
	}

}
