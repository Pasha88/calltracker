<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/cash_oper/InsertCashOperationCommand.php');


class CashOperRepo extends Repository {

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

    public function saveOperation($operation) {
        $params = array('operation' => $operation);
        $c = new InsertCashOperationCommand($params);
        return  $this->executeTransaction($c);
    }

}