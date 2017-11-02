<?php

class Call {

	public $call_object_id;
	public $client_id;
	public $call_date_time;
	public $type_id;
	public $customer_id;
	public $number;
    public $numberId;
	public $description;

	public $dateTimeOffset;
	
	function __construct($bdArray)
	{
		$this->call_object_id= $bdArray['call_object_id'];
		$this->client_id= $bdArray['client_id'];
		$this->call_date_time= $bdArray['call_date_time'];
		$this->type_id= $bdArray['type_id'];
		$this->number= isset($bdArray['number']) ? $bdArray['number'] : 'Нет свободного номера';
		$this->description= $bdArray['description'];
        $this->customerId= $bdArray['customer_id'];
        if(isset($bdArray['number_id'])) {
            $this->numberId = $bdArray['number_id'];
        }
        else if(isset($bdArray['id'])) {
            $this->numberId = $bdArray['id'];
        }
        else {
            $this->numberId = null;
        }
	}

	function setClientTimeOffset($offsetHour) {
	    if($offsetHour == null) {
	        return;
        }
        $dateTimeOffset = $offsetHour;
        $tmpDate = new DateTime($this->call_date_time, Util::getServerTz());
        $this->call_date_time = Util::formatDate(Util::convertToCustomerTz($tmpDate, $dateTimeOffset));
    }
}

?>