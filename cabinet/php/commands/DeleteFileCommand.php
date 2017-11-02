<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class DeleteFileCommand  extends Command {

    private $deleteFileSQL = 'delete from file_object where file_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->deleteFileSQL)) {
            $stmt->bind_param("i", $this->args['fileId']);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_DELETE_FILE->message);
        }

        return $this->resultOK();
    }
}