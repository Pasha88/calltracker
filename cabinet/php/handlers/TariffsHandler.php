<?php
require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/commands/TariffCommand.php");
require_once(dirname(__DIR__) . "/commands/GetTariffsCommand.php");

class TariffsHandler extends SimpleRest {

    public function getTariffList($customerUid) {
        $params = array('customerUid' => $customerUid);
        $command = new GetTariffsCommand($params);
        $this->handle($command);
    }

    public function saveTariffList($tariffList, $customerUid) {
        $params = array('customerUid' => $customerUid, 'tariffSet' => $tariffList);
        $command = new TariffCommand($params);
        $this->handle($command);
    }
}

