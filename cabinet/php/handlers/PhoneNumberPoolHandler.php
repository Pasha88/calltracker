<?php
require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/commands/SavePhoneNumberPoolCommand.php");
require_once(dirname(__DIR__)."/commands/GetPhoneNumberPoolCommand.php");
require_once(dirname(__DIR__) . "/commands/OccupyPhoneNumberPoolCommand.php");

		
class PhoneNumberPoolHandler extends SimpleRest {

	function getFreePhoneNumber($clientId, $customerUid, $phoneNumberId ,$yaClientId, $yaId, $url) {
        $params = array('clientId' => $clientId, 'customerUid' => $customerUid, 'phoneNumberId' => intval($phoneNumberId),
            'yaClientId' => $yaClientId, 'yaId' => $yaId, 'url' => $url);
        $command = new OccupyPhoneNumberPoolCommand($params);
        $this->handle($command);
	}

	public function getPhoneNumberList($customerUid) {
        $params = array('customerUid' => $customerUid);
        $command = new GetPhoneNumberPoolCommand($params);
        $this->handle($command);
    }

    public function savePhoneNumberList($phoneNumberList, $customerUid) {
        $params = array('customerUid' => $customerUid, 'numberPool' => $phoneNumberList);
        $command = new SavePhoneNumberPoolCommand($params);
        $this->handle($command);
    }
}

