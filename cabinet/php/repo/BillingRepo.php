<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/tariff/TariffListCommand.php');
require_once(dirname(__DIR__) . '/commands/cash_oper/InsertCashOperationCommand.php');
require_once(dirname(__DIR__) . '/commands/cash_oper/GetLastExpenseOperationCommand.php');
require_once(dirname(__DIR__) . '/commands/customer/SaveCustomerByUidCommand.php');
require_once(dirname(__DIR__) . '/repo/CashOperRepo.php');
require_once(dirname(__DIR__) . '/repo/TariffRepo.php');
require_once(dirname(__DIR__) . '/domain/CashOper.php');

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

            $lastOperation = count($lastOperation) > 0 ? $lastOperation[0] : null;

            $tariffRepo = TariffRepo::getInstance();
            $tariffHistory = $tariffRepo->getTariffHistory($customer->customerId);
            $firstDate = $tariffHistory[0]->setDate;

            $oneDay = new DateInterval('P1D');
            $operDate = Util::createCommonDate(isset($lastOperation) && isset($lastOperation->operDate) ? $lastOperation->operDate : $firstDate);
            $today = Util::getCurrentDate();

            $operDate->setTime(0,0);
            $today->setTime(0,0);
            $delta = $today->diff($operDate);

            for($i=1; $i<=$delta->days; $i++) {
                $operDate->add($oneDay);

                $tariff = $tariffRepo->tariffById($customer->tariffId);
                $sum = round(($tariff->rate/AppConfig::CASH_MOVE_KOEFF * -1), 2);

                $msg = AppConfig::CASH_MOVE_DEFAULT_DSC;
                $cashOperation = new CashOper(null, $customer->customerUid, Util::formatDate($operDate), $sum, $msg, null);

                $params = array('operation' => $cashOperation);
                $c = new InsertCashOperationCommand($params);
                $c->execute($conn);

                $customer->balance = $customer->balance + $sum;
                $msg = "Списание " . $sum . " " . AppConfig::DEFAULT_CURRENCY . " по тарифу [" . $tariff->tariffName . "]. Текущий баланс после списания: " . $customer->balance . " " . AppConfig::DEFAULT_CURRENCY;

                $c = new SaveCustomerByUidCommand($customer);
                $c->execute($conn);
                $conn->commit();
            }
        }
        catch(Exception $ex) {
            $conn->rollback();
            $this->close($conn);
            throw new Exception($ex->getMessage());
        }
        $this->close($conn);
        return $msg;
    }

}