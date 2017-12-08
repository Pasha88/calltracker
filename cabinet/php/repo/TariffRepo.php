<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/tariff/TariffListCommand.php');
require_once(dirname(__DIR__) . '/commands/tariff/TariffByIdCommand.php');
require_once(dirname(__DIR__) . '/commands/tariff/TariffHistoryCommand.php');
require_once(dirname(__DIR__) . '/commands/tariff/SaveTariffListCommand.php');
require_once(dirname(__DIR__) . '/commands/tariff/UserTariffCommand.php');

class TariffRepo extends Repository {

    private static $_instance = null;

    private function __construct() {}
    protected function __clone() {}

    static public function getInstance() {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function tariffById($id) {
        $params = array('id' => $id, 'isDeleted' => null);
        $c = new TariffByIdCommand($params);
        return  $this->executeTransaction($c);
    }

    public function tariffByIdNotDeleted($id) {
        $params = array('id' => $id, 'isDeleted' => 0);
        $c = new TariffByIdCommand($params);
        return  $this->executeTransaction($c);
    }

    public function tariffList() {
        $c = new TariffListCommand(null);
        return  $this->executeTransaction($c);
    }

    public function saveTariffList($tariffList) {
        $params = array('tariffList' => $tariffList);
        $c = new SaveTariffListCommand($params);
        return  $this->executeTransaction($c);
    }

    public function getTariffHistory($customerId) {
        $params = array('customerId' => $customerId);
        $c = new TariffHistoryCommand($params);
        return  $this->executeTransaction($c);
    }

    public function getUserTarif($customerUid) {
        $params = array('customerUid' => $customerUid);
        $c = new UserTariffCommand($params);
        return  $this->executeTransaction($c);
    }
}