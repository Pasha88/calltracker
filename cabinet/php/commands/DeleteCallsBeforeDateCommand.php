<?php

require_once(dirname(__DIR__) . '/commands/Command.php');

class DeleteCallsBeforeDateCommand  extends Command {

    private $deleteCallsBeforeDateSQL = 'delete from call_object where call_date_time < ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customer = null;
        if ($stmt = $conn->prepare($this->deleteCallsBeforeDateSQL)) {
            $stmt->bind_param("s", $this->args['beforeDate']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_DELETE_CALLS_BEFORE_DATE->message);
        }
        return true;
    }
}