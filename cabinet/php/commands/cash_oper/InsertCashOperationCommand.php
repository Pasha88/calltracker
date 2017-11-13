<?php

require_once (dirname(__DIR__)."../commands/Command.php");

class InsertCashOperationCommand extends Command{

    private $insertCashOperationSQL = 'insert into balance_operation(customer_uid, oper_date, sum, dsc, order_id)
                      values(?,?,?,?,?, (case when ? is not null then unhex(replace(?,\'-\',\'\') else null end))';

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $operation = $this->args['operation'];
        if ($stmt = $conn->prepare($this->insertCashOperationSQL)) {
            $stmt->bind_param("ssdsss",
                $operation->customerUid,
                $operation->operDate,
                $operation->sum,
                $operation->dsc,
                $operation->orderId,
                $operation->orderId
            );
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_INSERT_CASH_OPERATION->message);
        }

        return true;
    }

}