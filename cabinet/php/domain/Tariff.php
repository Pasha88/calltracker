<?php

require_once(dirname(__DIR__)."/util/Util.php");

Class Tariff {
	
	public $tariffId;
	public $tariffName;
	public $maxPhoneNumber;
	public $rate;
    public $is_deleted;

    public static function create($bdRow) {
        $result = new Tariff();
        if($bdRow == null) {
            $result->tariff_id = -1;
            $result->tariff_name = '0';
            $result->max_phone_number = 0;
            $result->rate = 0.00;
            $result->is_deleted = 1;
        }
        else {
            $result->tariff_id = $bdRow['tariff_id'];
            $result->tariff_name = $bdRow['tariff_name'];
            $result->max_phone_number = $bdRow['max_phone_number'];
            $result->rate = (float) $bdRow['rate'];
            $result->is_deleted = $bdRow['is_deleted'];
        }
        return $result;
    }

    public static function createDefault($customerId, $number) {
        $result = new Tariff();

            $result->tariff_id = -1;
            $result ->tariff_name = '0';
            $result->max_phone_number = 0;
            $result->rate = 0.00;
            $result->is_deleted = 1;

        return $result;
    }

}
