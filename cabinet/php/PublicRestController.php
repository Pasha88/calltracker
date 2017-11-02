<?php

//header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Headers: Content-Type');

function catch_fatal_error()
{
    // Getting Last Error
    $last_error =  error_get_last();

    // Check if Last error is of type FATAL
	if(isset($last_error))
		  {
				switch ($last_error['type']) {
					case E_ERROR:
                    case E_PARSE:
                    case E_CORE_ERROR:
                    case E_COMPILE_ERROR:
						$requestContentType = $_SERVER['HTTP_ACCEPT'];
						$requestContentType .= "charset=utf-8";
						$statusMessage = 'Internal Server Error';
						header("HTTP/1.1 ". 500 ." ". $statusMessage);
						header("Content-Type:". $requestContentType);
						if(strpos($requestContentType,'application/json') !== false) {
						  $rawData = new stdClass();
						  $rawData->error = "Fatal PHP error -> " . $last_error['message'];
						  $jsonResponse = json_encode($rawData);
						  echo(Util::convertUnicodeCodepoint($jsonResponse));
						}
						break;
					default:
				}
		  }
}
register_shutdown_function('catch_fatal_error');

require_once("handlers/AuthHandler.php");
require_once("handlers/CustomerHandler.php");

$view = "";
if(isset($_GET["view"])) {
	$view = $_GET["view"];
}

$json = file_get_contents('php://input');
$requestObj = json_decode($json);

/*
controls the RESTful services
URL mapping
*/
switch($view){

	case "register":
		$authHandler = new AuthHandler();
		$authHandler->register($requestObj->login, $requestObj->hkey);
		break;	
		
	case "auth":
		$authHandler= new AuthHandler();
		$authHandler->check($requestObj->login, $requestObj->hkey);
		break;

    case "recallpwd":
        $authHandler= new AuthHandler();
        $authHandler->recallPwd($requestObj->email);
        break;

    case "restorepwd":
        $authHandler= new AuthHandler();
        $authHandler->restorePwd($_GET['recallUID'], $_GET['customerUid']);
        break;

	case "resetPwd":
        $authHandler= new AuthHandler();
        $authHandler->restoreForgottenPwd($requestObj->customerUid, $requestObj->token, $requestObj->newPwd);
        break;
    case "numloader":
        $authHandler= new CustomerHandler();
        $authHandler->loadNumHandler($_GET['customerUid']);
        break;

				
	case "" :
		//404 - not found;
		break;
}
?>
