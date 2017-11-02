<?php

require_once(dirname(__DIR__) . '/commands/Command.php');
require_once(dirname(__DIR__) . '/domain/PhoneNumber.php');

class FindNumberPoolCommand  extends Command {

    private $getNumberPoolSQL = 'select * from number_pool where id = ?';
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

            $phoneNumber = new PhoneNumber();

            $stmt->bind_result(
                $phoneNumber->id,
                $phoneNumber->customerId,
                $phoneNumber->number,
                $phoneNumber->description,
                $phoneNumber->freeDateTime
            );
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_NUMBER_POOL->message);
        }

        if($phoneNumber == null || $phoneNumber->id == null) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_NUMBER_POOL_NOT_EXISTS->message);
        }

        return $phoneNumber;
    }
}