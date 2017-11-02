<?php

require_once('Command.php');

class SaveFileCommand extends Command {

    private $saveFileSQL = "INSERT INTO file_object (filename, tmp_filename, filesize) VALUES (?,?,?)";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $file = $this->args['file'];

        $fileName = $conn->real_escape_string($file['name']);
        $fileTmpName = $conn->real_escape_string($file['tmp_name']);
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileContent = $conn->real_escape_string( file_get_contents($fileTmpName) );

        $saveFileSQLquery = "INSERT INTO file_object (filename, file_content, tmp_filename, filesize, file_type) VALUES ('$fileName','$fileContent','$fileTmpName', '$fileSize', '$fileType')";

        $qresult = $conn->query($saveFileSQLquery);

        $last_id = $conn->insert_id;

        if($last_id <= 0 || $qresult == false) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_SAVE_FILE->message);
        }

        $result = new stdClass();
        $result->fileId = $last_id;

        return $result;
    }
}