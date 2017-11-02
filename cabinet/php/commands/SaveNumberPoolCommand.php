<?php

require_once(dirname(__DIR__) . '/commands/Command.php');
require_once(dirname(__DIR__) . '/domain/PhoneNumber.php');

class SaveNumberPoolCommand  extends Command {

    private $getNumberPoolSQL = 'update number_pool set phone_number = ?, description = ?, free_date_time = ?, customer_id = ? where id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customer = null;
        if ($stmt = $conn->prepare($this->getNumberPoolSQL)) {
            $stmt->bind_param("sssii", $this->args['number'], $this->args['description'], $this->args['freeDateTime'], $this->args['customerId'], $this->args['id']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_NUMBER_POOL->message);
        }

        return true;
    }
}