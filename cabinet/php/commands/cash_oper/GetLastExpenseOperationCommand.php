<?php

require_once (dirname(__DIR__)."/../commands/Command.php");

class GetLastExpenseOperationCommand extends Command{

    private $getExpenseOperSQL = "select * from balance_operation where customer_uid = ? and sum <= 0 and dsc like '%списание%' order by oper_date desc limit 1";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customerUidParam = $this->args['customerUid'];

        $cashOperations = [];

        $operId = null;
        $customerUid = null;
        $operDate = null;
        $sum = null;
        $dsc = null;
        $orderId = null;

        if ($stmt = $conn->prepare($this->getExpenseOperSQL)) {
            $stmt->bind_param("s", $customerUidParam);
            $stmt->bind_result($operId, $customerUid, $operDate, $sum, $dsc, $orderId);
            $stmt->execute();
            $stmt->store_result();
            while($stmt->fetch() != false) {
                $cashOperation = new CashOper($operId, $customerUid, $operDate, $sum, $dsc, Util::bin2uuidString($orderId));
                array_push($cashOperations, $cashOperation);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_EXPENSE_OPERATIONS->message);
        }

        return $cashOperations;
    }

}