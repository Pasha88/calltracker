<?php
    require_once("SimpleRest.php");
    require_once(dirname(__DIR__) . "/commands/SaveUserTariffCommand.php");
    require_once(dirname(__DIR__) . "/commands/GetUserTariffCommand.php");

    class UserTariffHandler extends SimpleRest {

        public function getUserTariff($customerUid) {
            $params = array('customerUid' => $customerUid);
            $command = new GetUserTariffCommand($params);
            $this->handle($command);
        }

        public function saveUserTariff($selectedTariff, $customerUid) {
            $params = array('customerUid' => $customerUid, 'selectedTariff' => $selectedTariff);
            $command = new SaveUserTariffCommand($params);
            $this->handle($command);
        }
}

