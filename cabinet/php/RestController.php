<?php

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


require_once("handlers/PhoneNumberPoolHandler.php");
require_once("handlers/AuthHandler.php");
require_once("handlers/CallsHandler.php");
require_once("handlers/CustomerHandler.php");
require_once("handlers/FileHandler.php");
require_once("handlers/SupportHandler.php");
require_once("handlers/RawDataHandler.php");
require_once("repo/AppPropertyRepo.php");
require_once("repo/CustomerRepo.php");
require_once("util/YaUtil.php");
require_once("repo/PhoneNumberPoolRepo.php");
require_once("repo/OrderStatusRepo.php");
require_once("repo/OrderRepo.php");

$view = "";
if(isset($_GET["view"])) {
	$view = $_GET["view"];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$json = file_get_contents('php://input');
	$requestObj = json_decode($json);
}

function forbid($msg) {
    header("HTTP/1.1 403 Forbidden");
    header("Content-Type: application/json; charset=UTF-8"); // . $_SERVER['HTTP_ACCEPT']
    $result = new stdClass();
    $result->error = $msg;
    echo json_encode($result);
    return;
}

$headers = apache_request_headers();
if(isset($headers["Authorization"])) {
	$token = preg_replace("/Bearer/", "", $headers["Authorization"]);  ;
	if(!AuthHandler::checkToken( trim($token) )) {
        forbid("Время доступа истекло или сессия недействительна");
        return;
	}
}
else {
    forbid("Не найден токен авторизации");
    return;
}

switch ($view) {

    case "savePhoneList":
        $numberRestHandler = new PhoneNumberPoolHandler();
        $numberRestHandler->savePhoneNumberList($requestObj->phoneNumberList, $requestObj->customerUid);
        break;

    case "getPhoneList":
        $numberRestHandler = new PhoneNumberPoolHandler();
        $numberRestHandler->getPhoneNumberList($requestObj->customerUid);
        break;

    case "callspage":
        $callsHandler = new CallsHandler();
        $callsHandler->getCallsPage($requestObj->filters);
        break;

    case "callstatechange":
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $callsHandler = new CallsHandler();
        $callsHandler->callStateChange($requestObj->id, $requestObj->typeId, $requestObj->numberId, $actual_link);
        break;

    case "resetPwd":
        $authHandler = new AuthHandler();
        $authHandler->resetPwd($requestObj->customerUid, $requestObj->oldPwd, $requestObj->newPwd);
        break;

    case "saveGaId":
        $authHandler = new CustomerHandler();
        $authHandler->saveGaId($requestObj->customerUid, $requestObj->gaId);
        break;

    case "saveDefaultNumber":
        $authHandler = new CustomerHandler();
        $authHandler->saveDefaultNumber($requestObj->customerUid, $requestObj->number, $requestObj->domain);
        break;

    case "deletecall":
        $callsHandler = new CallsHandler();
        $callsHandler->deleteCall($requestObj->id);
        break;

    case "hasNewCalls":
        $callsHandler = new CallsHandler();
        $callsHandler->hasNewCalls($requestObj->lastCallId);
        break;

    case "uploadFile":
        $fileHandler = new FileHandler();
        if(count($_FILES) > 1) {
            $statusCode = 500;
            $rawData = new stdClass();
            $rawData->error = "Upload only single files";
            error_log(Util::normErrMsg("[ " . Util::getCurrentDateFormatted() . " ] ===> (" . $rawData->error . ")"), 0);
            $this->echoResponse($statusCode, $rawData);
        }
        $fileHandler->save($_FILES['file']);
        break;

    case "deleteFile":
        $fileHandler = new FileHandler();
        $fileHandler->delete($requestObj->fileId);
        break;

    case "supportRequest":
        $fileHandler = new SupportHandler();
        $fileHandler->createRequest($requestObj->requestText, $requestObj->fileArray, $requestObj->customerUid);
        break;

    case "checkinstall":
        $customerHandler = new CustomerHandler();
        $customerHandler->checkInstall($requestObj->customerUid);
        break;

    case "loadUserSettings":
        $customerHandler = new CustomerHandler();
        $customerHandler->loadUserSettings($requestObj->customerUid);
        break;

    case "saveUserSettings":
        $customerHandler = new CustomerHandler();
        $customerHandler->saveUserSettings($requestObj->customerUid, $requestObj->customerTimeZone, $requestObj->upTimeFrom, $requestObj->upTimeTo, $requestObj->upTimeSchedule);
        break;

    case "loadMainSettings":
        $repo = AppPropertyRepo::getInstance();
//        $repo.checkRole();
        $properties = $repo->readAll();
        $handler = new RawDataHandler();
        $result = new stdClass();
        $result->settings = $properties;
        $handler->handleResult($result);
        break;

    case "saveMainSettings":
        $repo = AppPropertyRepo::getInstance();
        $result = $repo->saveAll($requestObj->settings);
        $handler = new RawDataHandler();
        $handler->handleResult($result);
        break;

    case "saveYaId":
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomerByUid($requestObj->customerUid);
        $customer->yaId =  $requestObj->yaId;
        $customer->restore_uid = $requestObj->guid; // Используем поле restore_uid для получения и сохранения токена яндекс
        $repo->saveCustomer($customer);
        $handler = new RawDataHandler();
        $res = new stdClass();
        $res->result = true;
        $res->appId = AppConfig::YA_APPLICATION_ID;
        $handler->handleResult($res);
        break;

    case 'confirmYaId':
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomerByUid($requestObj->customerUid);
        $handler = new RawDataHandler();
        $result = new stdClass();

        if($customer->restore_uid != $requestObj->state) {
            $errMsg = "Неверный параметр state при получении токена яндекс";
            $result->success = false;
            $result->error = $errMsg;
            $handler->handleResult($result);
            return;
        }
        $customerHandler = new CustomerHandler();
        $response = $customerHandler->confirmYaToken($requestObj->code);

        if(!isset($response) || !isset($response['token_type'])) {
            throw new Exception("Ошибка получения токена яндекс (получены неверные данные)");
        }

        $customer->yaIdAuth = $response['access_token'];
        $customer->yaRefresh = $response['refresh_token'];

        $ya = new YaUtil();
        if($ya->checkYaCounterId($customer->yaId, $customer->yaIdAuth) == false) {
            $customer->yaId =  null;
            $customer->yaIdAuth = null;
            $customer->yaRefresh = null;
            $repo->saveCustomer($customer);
            $handler->handleError('Ошибка авторизации счетчика. Убедитесь, что вы вошли в Яндекс с учетной записью, которая имеет доступ к этому счетчику. 
                                    <br><a href="https://passport.yandex.ru/auth" target="_blank"><u>Перейти к выбору аккаунта</u></a>');
            return;
        }

        $d = Util::getCurrentDate();
        $d->add(new DateInterval('PT'. $response['expires_in'] .'S'));
        $customer->yaExpires = $d->getTimestamp();
        $customer->restore_uid = null;
        $repo->saveCustomer($customer);
        $result->success = true;
        $handler->handleResult($result);
        break;

    case 'setNoYaAuth':
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomerByUid($requestObj->customerUid);
        $customer->yaIdAuth = AppConfig::YA_TOKEN_NOT_VALID_OK;
        $repo->saveCustomer($customer);
        $handler = new RawDataHandler();
        $handler->result = true;
        break;

    case "freeNumber":
        $repo = PhoneNumberPoolRepo::getInstance();
        $number = $repo->getPhoneNumberPool($requestObj->id);
        $dt = new DateTime('01-01-1970 01:00');
        $number->freeDateTime = $dt->format(Util::COMMON_DATE_TIME_FORMAT);
        $repo->savePhoneNumberPool($number);
        $handler = new RawDataHandler();
        $handler->result = true;
        break;

    case "freeNumbers":
        $repo = PhoneNumberPoolRepo::getInstance();
        $numbers = $repo->getAllNumbers();
        foreach($numbers as $number) {
            $dt = new DateTime('01-01-1970 01:00');
            $number->freeDateTime = $dt->format(Util::COMMON_DATE_TIME_FORMAT);
            $repo->savePhoneNumberPool($number);
        }
        $handler = new RawDataHandler();
        $handler->result = true;
        break;

    case "allOrderStatuses":
        $repo = OrderStatusRepo::getInstance();
        $statuses = $repo->getAll();
        $handler = new RawDataHandler();
        $result = new stdClass();
        $result->statuses = $statuses;
        $handler->handleResult($result);
        break;

    case "getOrders":
        $repo = OrderRepo::getInstance();
        $list = $repo->orderList($requestObj->filters);
        $handler = new RawDataHandler();
        $result = new stdClass();
        $result->orders = $list->orders;
        $result->totalPages = $list->totalPages;
        $handler->handleResult($result);
        break;

    default:
        forbid("Не найден метод");
}
