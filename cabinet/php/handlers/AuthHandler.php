<?php
require_once("SimpleRest.php");
require_once(dirname(__DIR__)."/commands/RegisterCustomerCommand.php");
require_once(dirname(__DIR__)."/commands/CheckCredentialsCommand.php");
require_once(dirname(__DIR__)."/commands/UpdateCustomerPwdCommand.php");
require_once(dirname(__DIR__)."/commands/RecallPwdCommand.php");
require_once(dirname(__DIR__)."/commands/RestorePwdCommand.php");
require_once(dirname(__DIR__)."/commands/ResetForgottenPwdCommand.php");
require_once(dirname(__DIR__)."/commands/customer/FindCustomerCommand.php");

class AuthHandler extends SimpleRest {

    public static function checkToken($token) {
    	try {
    		return JWTTool::checkToken($token, $_SERVER['HTTP_ORIGIN']);
    	}
    	catch(Exception $ex) {
    		return false;
    	}
    }

    public function checkCustomerToken($token, $customerUid)    {
        try {
            $command = new FindCustomerCommandByUid(array('customerUid' => $customerUid));
            $customer = $this->handleWithResult($command);
            return JWTTool::checkCustomerToken($token, $_SERVER['HTTP_ORIGIN'], $customer->email); //
        }
        catch(Exception $ex) {
            return false;
        }
    }

    function register($email, $hkey) {	
        $params = array('email' => $email, 'hkey' => $hkey);
        $command = new RegisterCustomerCommand($params);
        $this->handle($command);
	}
	
	function check($email, $hkey) {
        $params = array('email' => $email, 'hkey' => $hkey);
        $command = new CheckCredentialsCommand($params);
        $this->handle($command);
	}
    function resetPwd($customerUid, $oldPwd, $newPwd) {
        $params = array('customerUid' => $customerUid, 'oldPwd' => $oldPwd, 'newPwd' => $newPwd);
        $command = new UpdateCustomerPwdCommand($params);
        $this->handle($command);
    }

    function recallPwd($email) {
        $params = array('email' => $email);
        $command = new RecallPwdCommand($params);
        $this->handle($command);
    }

    function restorepwd($restoreToken, $customerUid) {
        $params = array('token' => $restoreToken, 'customerUid' => $customerUid);
        $command = new RestorePwdCommand($params);
        $this->handle($command);
    }

    function restoreForgottenPwd($customerUid, $token, $newPwd)    {
        $params = array('customerUid' => $customerUid, 'token' => $token, 'newPwd' => $newPwd);
        $command = new ResetForgottenPwdCommand($params);
        $this->handle($command);
    }

}

