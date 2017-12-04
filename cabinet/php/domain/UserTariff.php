<?php

require_once(dirname(__DIR__)."/util/Util.php");

Class UserTariff {
	
	public $tariffId;
	public $tariffName;
	public $setDate;
    public $maxPhoneNumber;
    public $rate;

    public static function create($bdRow) {
        $result = new UserTariff();
        if($bdRow == null) {
            $result->tariffId = -1;
            $result->tariffName = '0';
            $result->maxPhoneNumber = 0;
            $result->rate = 0.00;
            $result->setDate = '1970-01-01';
        }
        else {
            $result->tariffId = $bdRow['tariff_id'];
            $result->tariffName = $bdRow['tariff_name'];
            $result->maxPhoneNumber = $bdRow['max_phone_number'];
            $result->rate = $bdRow['rate'];
            $result->setDate = $bdRow['set_date'];
        }
        return $result;
    }

    public static function createDefault() {
        $result = new UserTariff();

        $result->tariffId = -1;
        $result->tariffName = '0';
        $result->maxPhoneNumber = 0;
        $result->rate = 0.00;
        $result->setDate = '1970-01-01';

        return $result;
    }
}
