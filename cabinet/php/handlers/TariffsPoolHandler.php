<?php
require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/commands/TariffPoolCommand.php");
require_once(dirname(__DIR__)."/commands/GetTariffsPoolCommand.php");

class TariffsPoolHandler extends SimpleRest {

    public function getPhoneNumberList($customerUid) {
        $params = array('customerUid' => $customerUid);
        $command = new GetTariffsPoolCommand($params);
        $this->handle($command);
    }

    public function savePhoneNumberList($phoneNumberList, $customerUid) {
        $params = array('customerUid' => $customerUid, 'numberPool' => $phoneNumberList);
        $command = new TariffPoolCommand($params);
        $this->handle($command);
    }
}

