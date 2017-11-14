<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/tariff/TariffListCommand.php');
require_once(dirname(__DIR__) . '/commands/cash_oper/InsertCashOperationCommand.php');
require_once(dirname(__DIR__) . '/commands/customer/SaveCustomerByUidCommand.php');
require_once(dirname(__DIR__) . '/repo/CashOperRepo.php');

class BillingRepo extends Repository {

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

    public function dailyMove($customer) {

        $conn = $this->init();

        $msg = null;

        try {
            $params = array('customerUid' => $customer->customerUid);
            $c = new GetLastExpenseOperationCommand($params);
            $lastOperation = $c->execute($conn);

            $oneDay = new DateInterval('P1D');
            $operDate = Util::createCommonDate($lastOperation->operDate);
            $today = Util::getCurrentDate();
            $delta = $today->diff($operDate);

            for($i=1; $i<=$delta->days; $i++) {
                $operDate->add($oneDay);

                $tariffRepo = TariffRepo::getInstance();
                $tariff = $tariffRepo->tariffById($customer->tariffId);
                $sum = $tariff->rate/AppConfig::CASH_MOVE_KOEFF;

                $msg = AppConfig::CASH_MOVE_DEFAULT_DSC * -1;
                $cashOperation = new CashOper(null, $customer->customerUid, $operDate, $sum, $msg, null);

                $params = array('operation' => $cashOperation);
                $c = new InsertCashOperationCommand($params);
                $c->execute($conn);

                $customer->balance = $customer->balance + $sum;
                $msg = "Списание " . $sum . " " . AppConfig::DEFAULT_CURRENCY . " по тарифу " . $tariff->tariffName . ". Текущий баланс после списания: " . $customer->balance . " " . AppConfig::DEFAULT_CURRENCY;

                $c = new SaveCustomerByUidCommand($customer);
                $c->execute($conn);
            }

        }
        catch(Exception $ex) {
            $conn->rollback();
            $this->close($conn);
            throw new Exception($ex->getMessage());
        }

        $conn->commit();
        $this->close($conn);

        return $msg;
    }

}