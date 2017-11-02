<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Customer.php');

class GetAllCustomersIdCommand  extends Command {

    private $getAllCustomersIdSQL = 'select customer_id from customer';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customer = null;
        if ($stmt = $conn->prepare($this->getAllCustomersIdSQL)) {
            $customerId = null;
            $stmt->bind_result($customerId);
            $stmt->execute();

            $customerIdArray = array();
            while($stmt->fetch() != false) {
                array_push($customerIdArray, $customerId);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_ALL_CUSTOMER_ID->message);
        }

        return $customerIdArray;
    }
}