<?php

require_once (dirname(__DIR__)."/Command.php");

class SetCallYaUploadStateCommand extends Command {

    private $updateCallStateSQL = 'update call_object set ya_upload = ?, modify_date = ? where call_object_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $callObjectId = null;

        if ($stmt = $conn->prepare($this->updateCallStateSQL)) {
            $modifyDate = Util::getCurrentDateFormatted();
            $stmt->bind_param("isi", $this->args['yaUploadState'], $modifyDate, $callObjectId);

            foreach ($this->args['callIdArray'] as $id) {
                $callObjectId = $id;
                $stmt->execute();
            }

            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_CALL_STATE->message);
        }

        $result = new stdClass();
        $result->code = 0;
        return $result;
    }

}