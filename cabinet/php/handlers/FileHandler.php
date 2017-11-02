<?php

require_once(dirname(__DIR__)."/commands/SaveFileCommand.php");
require_once(dirname(__DIR__)."/commands/DeleteFileCommand.php");
require_once(dirname(__DIR__)."/commands/CreateRequestCommand.php");

class FileHandler extends SimpleRest {

    public function save($file) {
        $params = array('file' => $file);
        $command = new SaveFileCommand($params);
        $this->handle($command);
    }

    public function delete($fileId) {
        $params = array('fileId' => $fileId);
        $command = new DeleteFileCommand($params);
        $this->handle($command);
    }

}