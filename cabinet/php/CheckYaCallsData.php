<?php

require_once(dirname(__DIR__) . '/php/util/Repository.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/GetYaCallsCSVCommand.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/SetCallYaUploadStateCommand.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/GetNoFreeNumberEventsCSVCommand.php');
require_once(dirname(__DIR__) . '/php/commands/customer/GetAllCustomersCommand.php');
require_once(dirname(__DIR__) . '/php/util/YaUtil.php');
require_once(dirname(__DIR__) . '/php/util/Util.php');
require_once(dirname(__DIR__) . '/php/repo/CustomerRepo.php');

$r = new Repository();

//$res = "";
$e = new YaUtil();

$customerId = isset($_GET['id']) ? intval($_GET['id']) : null;

if(isset($customerId)) {
    $repo = CustomerRepo::getInstance();
    $customer = $repo->getCustomer($customerId);

    print("-------------------------- Customer: [" . $customer->email. "] ---------------------------------- \r\n<br>");

    if(!isset($customer->yaId) || strlen(trim($customer->yaId)) == 0) {
        print("No yandex ID\r\n");
    }

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID || !isset($customer->yaIdAuth)) {
        error_log("Wrong client Yandex.Metrika token [ID=" .  $customerId . "]");
        print("Wrong client Yandex.Metrika token\r\n<br>");
    }

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID_OK) {
        print("Yandex.Metrika token not valid but client pressed \"Do not remind\" button\r\n<br>");
    }

    if($customer->yaExpires - Util::getCurrentDate()->getTimestamp() < 300) { // Если токен истекает через пять минут или меньше
        $ya = new YaUtil();
        $ya->updateYaToken($customerId);
    }

    $response = $e->getCallLoads($customerId);
//    $res = $res . $response . "\r\n<br>";
    print($response . "\r\n<br>");
    return;
}

$c1 = new GetAllCustomersCommand(array());
$customers = $r->executeTransaction($c1);

foreach ($customers as $customer) {
    print("-------------------------- CUSTOMER [" . $customer->email. "]----------------------------\r\n<br>");
    $customerId = $customer->customerId;
    $response = "";

    if(!isset($customer->yaId) || strlen(trim($customer->yaId)) == 0) {
        print("No yandex ID\r\n<br>");
    }

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID || !isset($customer->yaIdAuth)) {
        error_log("Wrong client Yandex.Metrika token [ID=" .  $customerId . "]");
        print("Wrong client Yandex.Metrika token\r\n<br>");
        continue;
    }

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID_OK) {
        print("Yandex.Metrika token not valid but client pressed \"Do not remind\" button\r\n<br>");
        continue;
    }

    if($customer->yaExpires - Util::getCurrentDate()->getTimestamp() < 300) { // Если токен истекает через пять минут или меньше
        $ya = new YaUtil();
        $ya->updateYaToken($customerId);
    }
    $response = $e->getCallLoads($customerId);
    print($response . "\r\n<br>");
//    $res = $res . $response . "\r\n<br>";
}

print("--------------------------------------");
