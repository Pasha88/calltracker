<?php

require_once(dirname(__DIR__)."/util/Util.php");

Class Tariff {
	
	public $tariffId;
	public $tariffName;
	public $maxPhoneNumber;
	public $rate;
    public $isDeleted;

    public static function create($bdRow) {
        $result = new Tariff();
        $result->tariffId = $bdRow['tariff_id'];
        $result->tariffName = $bdRow['tariff_name'];
        $result->maxPhoneNumber = $bdRow['max_phone_number'];
        $result->rate = (float) $bdRow['rate'];
        $result->isDeleted = $bdRow['is_deleted'];
        return $result;
    }

    public static function createByArg($tariffId, $tariffName, $maxPhoneNumber, $rate, $isDeleted) {
        $result = new Tariff();
        $result->tariffId = $tariffId;
        $result->tariffName = $tariffName;
        $result->maxPhoneNumber = $maxPhoneNumber;
        $result->rate = (float) $rate;
        $result->isDeleted = $isDeleted;
        return $result;
    }

    public static function createDefault() {
        $result = new Tariff();

        $result->tariff_id = -1;
        $result ->tariff_name = '0';
        $result->max_phone_number = 0;
        $result->rate = 0.00;
        $result->is_deleted = 1;

        return $result;
    }
}
