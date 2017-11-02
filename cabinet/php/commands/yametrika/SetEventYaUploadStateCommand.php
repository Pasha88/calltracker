<?php

require_once(dirname(__DIR__) . 'Command.php');

class SetEventYaUploadStateCommand extends Command {

    private $setEventYaUploadStateSQL = 'update call_event set ya_upload = ? where call_event_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $callEventId = null;

        if ($stmt = $conn->prepare($this->setEventYaUploadStateSQL)) {

            $stmt->bind_param("ii", $this->args['yaUploadState'], $callEventId);

            foreach ($this->args['eventIdArray'] as $id) {
                $callEventId = $id;
                $stmt->execute();
            }

            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_CALL_EVENT_STATE->message);
        }

        $result = new stdClass();
        $result->code = 0;
        return $result;
    }

}