<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once (dirname(__DIR__)."/domain/Customer.php");
require_once (dirname(__DIR__)."/util/JWTTool.php");

class CheckCredentialsCommand extends Command {

    private $getCustomerSQL = 'select c.*, t.tariff_name from customer c left join tariff t on t.tariff_id = c.tariff_id where c.email = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $customer = null;

        if ($stmt = $conn->prepare($this->getCustomerSQL)) {
            $stmt->bind_param("s", $this->args['email']);

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
                $customer->balance,
                $customer->tariffName
            );

            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_CUSTOMER_BY_EMAIL->message);
        }

        if($customer == null || $customer->customerId == null) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CUSTOMER_NOT_EXISTS->message);
        }

        if(!password_verify($this->args['hkey'], $customer->pwdHash)) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_PASSWORD_ERROR->message);
        }

        $token = JWTTool::createToken($this->args['email'], $_SERVER['HTTP_ORIGIN'], JWTTool::$expireTokenPeriodSecYear);
        $result = new stdClass();
        $result->token = $token;

        $result->customerUid = $customer->customerUid;
        $result->role = $customer->role;
        $result->customerEmail = $this->args['email'];

        return $result;
    }

}