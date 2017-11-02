<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class CheckHasNewCallsCommand extends Command {

    private $checkHasNewCallSQL = 'select count(call_object_id) from call_object where call_object_id > ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $res = null;
        if ($stmt = $conn->prepare($this->checkHasNewCallSQL)) {
            $stmt->bind_param("i", $this->args['lastCallId']);
            $stmt->bind_result($res);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_CHECK_HAS_NEW_CALL_ERROR->message);
        }

        $rawData = new stdClass();
        $rawData->hasNewCalls = $res > 0;
        return $rawData;
    }

}
