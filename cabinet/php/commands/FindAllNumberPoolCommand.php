<?php

require_once(dirname(__DIR__) . '/commands/Command.php');
require_once(dirname(__DIR__) . '/domain/PhoneNumber.php');

class FindAllNumberPoolCommand  extends Command {

    private $getNumberPoolSQL = 'select * from number_pool';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customer = null;
        if ($stmt = $conn->prepare($this->getNumberPoolSQL)) {
            $stmt->bind_param("i", $this->args['id']);

            $id = null;
            $number = null;
            $description = null;
            $freeDateTime = null;
            $customerId = null;
            $stmt->bind_result(
                $id,
                $customerId,
                $number,
                $description,
                $freeDateTime
            );
            $stmt->execute();
            $phoneNumbers = array();
            while($stmt->fetch() != false) {
                $phoneNumber = new PhoneNumber();
                $phoneNumber->id = $id;
                $phoneNumber->number = $number;
                $phoneNumber->description = $description;
                $phoneNumber->freeDateTime = $freeDateTime;
                $phoneNumber->customerId = $customerId;
                array_push($phoneNumbers, $phoneNumber);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_NUMBER_POOL->message);
        }

        return $phoneNumbers;
    }
}