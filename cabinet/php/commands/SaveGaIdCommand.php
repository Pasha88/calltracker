<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class SaveGaIdCommand extends Command {


    private $saveGaIdSQL = 'update customer set ga_id = ? where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        if ($stmt = $conn->prepare($this->saveGaIdSQL)) {
            $stmt->bind_param("ss", $this->args['gaId'], $this->args['customerUid']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GA_ID_SAVE->message);
        }
    }
}