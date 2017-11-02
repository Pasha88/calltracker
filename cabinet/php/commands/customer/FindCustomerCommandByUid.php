<?php

require_once(dirname(__DIR__) . '/Command.php');

class FindCustomerCommandByUid  extends Command {

    private $getCustomerSQL = 'select * from customer where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customer = null;
        if ($stmt = $conn->prepare($this->getCustomerSQL)) {
            $stmt->bind_param("s", $this->args['customerUid']);

            $customer = new Customer();

            $stmt->bind_result(
                $customer->customerId,
                $customer->email,
                $customer->pwdHash,
                $customer->description,
                $customer->restore_uid,
                $customer->restore_valid_till,
                $customer->reset_pwd_uid,
                $customer->reset_pwd_valid_till,
                $customer->gaId,
                $customer->defaultNumber,
                $customer->defaultDomain,
                $customer->scriptToken,
                $customer->customerUid,
                $customer->timeZone,
                $customer->yaId,
                $customer->yaIdAuth,
                $customer->yaRefresh,
                $customer->yaExpires,
                $customer->role,
                $customer->upTimeFrom,
                $customer->upTimeTo,
                $customer->upTimeSchedule,
                $customer->tariffId,
                $customer->tariffName,
                $customer->balance
            );
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_CUSTOMER_BY_ID->message);
        }

        if($customer == null || $customer->customerId == null) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CUSTOMER_NOT_EXISTS->message);
        }

        return $customer;
    }
}