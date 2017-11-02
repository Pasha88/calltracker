<?php

require_once(dirname(__DIR__)."/commands/SaveFileCommand.php");
require_once(dirname(__DIR__)."/commands/DeleteFileCommand.php");
require_once(dirname(__DIR__)."/commands/CreateRequestCommand.php");

class SupportHandler extends SimpleRest {

    public function createRequest($requestText, $fileArray, $customerUid)    {
        $params = array('requestText' => $requestText, 'fileArray' => $fileArray, 'customerUid' => $customerUid);
        $command = new CreateRequestCommand($params);
        $this->handle($command);
    }
}