<?php

require_once("handlers/PhoneNumberPoolHandler.php");
require_once("handlers/AuthHandler.php");
require_once("handlers/CallsHandler.php");

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

function forbid() {
    header("HTTP/1.1 403 Forbidden");
    header("Content-Type: application/json; charset=UTF-8"); // . $_SERVER['HTTP_ACCEPT']
    return;
}

header("Content-Type: application/json; charset=UTF-8"); // . $_SERVER['HTTP_ACCEPT']
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

register_shutdown_function('catch_fatal_error');

$view = "";
if(isset($_GET["view"])) {
    $view = $_GET["view"];
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
    case 'OPTIONS':
        return;
    case 'POST':
        $json = file_get_contents('php://input');
        $requestObj = json_decode($json);
}

$headers = apache_request_headers();
if(isset($headers["X-Auth-Token"])) {
    $token = preg_replace("/Bearer/", "", $headers["X-Auth-Token"]);;
    $auth = new AuthHandler();
    if (!$auth->checkCustomerToken(trim($token), $requestObj->customerUid)) {
        forbid();
	return;
    }
}
else {
    forbid();
    return;
}

switch($view){
    case "getFreePhoneNumber":
        $numberRestHandler = new PhoneNumberPoolHandler();
        $numberRestHandler->getFreePhoneNumber($requestObj->clientId, $requestObj->customerUid, $requestObj->phoneNumberId, $requestObj->yaClientId, $requestObj->yaId, $requestObj->url);
        break;
    default :
        forbid();
        break;
}
