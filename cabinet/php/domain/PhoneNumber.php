<?php
require_once(dirname(__DIR__)."/util/Util.php");

Class PhoneNumber {
	public $id;
	public $number;
	public $description;
	public $freeDateTime;
	public $customerId;

    public static function create($bdRow) {
        $result = new PhoneNumber();
        if($bdRow == null) {
            $result->id = -1;
            $result ->customerId = -1;
            $result->number = 0;
            $result->description = "Нет номера";
            $result->freeDateTime = null;
        }
        else {
            $result->id = $bdRow['id'];
            $result ->customerId = $bdRow['customer_id'];
            $result->number = $bdRow['phone_number'];
            $result->description = $bdRow['description'];
            $result->freeDateTime = $bdRow['free_date_time'];
        }
        return $result;
    }

    public static function createDefault($customerId, $number) {
    	$result = new PhoneNumber();
    	$result->id = 0;
        $result->number = $number;
        $result->freeDateTime = 0;
        $result->customerId = $customerId;
        return $result;
    }
}
?>