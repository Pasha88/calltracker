<?php
require_once("SimpleRest.php");
require_once dirname(__DIR__)."/commands/SearchCallsCommand.php";
require_once dirname(__DIR__)."/commands/ChangeCallStateCommand.php";
require_once dirname(__DIR__)."/commands/DeleteCallCommand.php";
require_once dirname(__DIR__)."/commands/CheckHasNewCallsCommand.php";
 		
class CallsHandler extends SimpleRest {

	public function getCallsPage($filters) {
        $command = new SearchCallsCommand($filters);
        $this->handle($command);
	}

    public function callStateChange($callObjectId, $typeId, $numberId, $actual_link)    {
        $params = array('callObjectId' => $callObjectId, 'typeId' => $typeId, 'numberId' => $numberId, 'url' => $actual_link);
        $command = new ChangeCallStateCommand($params);
        $this->handle($command);
    }

    public function deleteCall($callId) {
        $params = array('callId' => $callId);
        $command = new DeleteCallCommand($params);
        $this->handle($command);
    }

    public function hasNewCalls($lastCallId) {
        $params = array('lastCallId' => $lastCallId);
        $command = new CheckHasNewCallsCommand($params);
        $this->handle($command);
    }


}