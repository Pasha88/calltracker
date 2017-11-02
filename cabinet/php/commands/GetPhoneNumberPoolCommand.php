<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class GetPhoneNumberPoolCommand extends Command {

    private $getPhoneNumberPoolSQL = 'select * from number_pool where customer_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);

        if ($stmt = $conn->prepare($this->getPhoneNumberPoolSQL)) {
            $stmt->bind_param("i", $customer->customerId);

            $row = array();
            $stmt->bind_result($row['id'], $row['customer_id'], $row['phone_number'],   $row['description'], $row['free_date_time']);
            $stmt->execute();

            $i=0;
            $resultArray = array();
            while ($stmt->fetch())
            {
                $resultArray[$i] = PhoneNumber::create($row);
                $i++;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        $result = new stdClass();
        $result->itemArray = $resultArray;
        $result->gaId = $customer->gaId;
        $result->defaultNumber = $customer->defaultNumber;
        $result->defaultDomain = $customer->defaultDomain;
        $result->yaId = $customer->yaId;
        $result->yaIdAuth = isset($customer->yaIdAuth) && $customer->yaIdAuth != AppConfig::YA_TOKEN_NOT_VALID_OK && $customer->yaIdAuth != AppConfig::YA_TOKEN_NOT_VALID
            ? $customer->yaIdAuth : null;
        return $result;
    }

}