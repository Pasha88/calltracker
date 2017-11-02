<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class DeleteCallCommand  extends Command {

    private $deleteCallSQL = 'delete from call_object where call_object_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->deleteCallSQL)) {
            $stmt->bind_param("i", $this->args['callId']);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_DELETE_CALL->message);
        }

        return $this->resultOK();
    }
}