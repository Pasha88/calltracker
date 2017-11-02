<?php

require_once(dirname(__DIR__) . '/php/util/Repository.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/GetYaCallsCSVCommand.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/SetCallYaUploadStateCommand.php');
require_once(dirname(__DIR__) . '/php/commands/yametrika/GetNoFreeNumberEventsCSVCommand.php');
require_once(dirname(__DIR__) . '/php/commands/customer/GetAllCustomersCommand.php');
require_once(dirname(__DIR__) . '/php/util/YaUtil.php');
require_once(dirname(__DIR__) . '/php/util/Util.php');

$r = new Repository();

print("Скрипт запущен \r\n<br>");

$customerId = isset($_GET['id']) ? intval($_GET['id']) : null;

if(isset($customerId)) {
    $repo = CustomerRepo::getInstance();
    $customer = $repo->getCustomer($customerId);

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID || !isset($customer->yaIdAuth)) {
        error_log("Wrong client Yandex.Metrika token [ID=" .  $customer->customerId . "]");
        print("Wrong client Yandex.Metrika token\r\n<br>");
    }
    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID_OK) {
        print("Yandex.Metrika token not valid but client pressed \"Do not remind\" button\r\n<br>");
    }
    if($customer->yaExpires - Util::getCurrentDate()->getTimestamp() < 300) { // Если токен истекает через пять минут или меньше
        $ya = new YaUtil();
        $ya->updateYaToken($customerId);
    }

    try {
        print("-------------------------- CUSTOMER [" . $customer->email. "]----------------------------\r\n<br>");
        $c = new GetYaCallsCSVCommand(array('customerId' => $customerId, 'lastTen' => $lastTenCalls));
        $resultHasCall = $r->executeTransaction($c);
        print("Звонки: \r\n<br>");
        print("$resultHasCall->csv \r\n<br>");

        $c = new GetNoFreeNumberEventsCSVCommand(array('customerId' => $customerId, 'lastTen' => $lastTenCalls));
        $resultNumberNotAcquired = $r->executeTransaction($c);
        print("Нет свободного номера: \r\n<br>");
        print("$resultNumberNotAcquired->csv\r\n<br>");

        $y = new YaUtil();

        if(count($resultHasCall->callIdArray) > 0) {
            $loadResult = $y->sendCallCsv($resultHasCall->csv, $customerId);
        }
        if(count($resultNumberNotAcquired->callIdArray) > 0) {
            $loadNoFreeNumberResult = $y->sendNoFreeNumberCsv($resultNumberNotAcquired->csv, $customerId);
        }

        if (isset($loadResult) &&  $loadResult == true) {
            $cd = new SetCallYaUploadStateCommand(array('yaUploadState' => 0, 'callIdArray' => $resultHasCall->callIdArray));
            $r->executeTransaction($cd);
        }

        if (isset($loadNoFreeNumberResult) && $loadNoFreeNumberResult == true) {
            $cd1 = new SetEventYaUploadStateCommand(array('yaUploadState' => 0, 'eventIdArray' => $resultNumberNotAcquired->eventIdArray));
            $r->executeTransaction($cd1);
        }
    } catch
    (Exception $ex) {
        error_log($ex->getMessage());
        print("Ошибка загрузки: [" . $ex->getMessage() . "]\r\n<br>");
        return;
    }
    print("Файл отправлен\r\n<br>");
    return true;
}

$c1 = new GetAllCustomersCommand(array());
$customers = $r->executeTransaction($c1);

foreach ($customers as $customer) {

    $customerId = $customer->customerId;
    print("-------------------------- CUSTOMER [" . $customer->email. "]----------------------------\r\n<br>");

    if($customer->yaIdAuth == AppConfig::YA_TOKEN_NOT_VALID || !isset($customer->yaIdAuth)) {
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

    try {
        $c = new GetYaCallsCSVCommand(array('customerId' => $customerId, 'lastTen' => $lastTenCalls));
        $resultHasCall = $r->executeTransaction($c);
        print("Звонков: " . count($resultHasCall->callIdArray) . "\r\n<br>");

        $c = new GetNoFreeNumberEventsCSVCommand(array('customerId' => $customerId, 'lastTen' => $lastTenCalls));
        $resultNumberNotAcquired = $r->executeTransaction($c);
        print("Нет свободного номера: " . count($resultNumberNotAcquired->eventIdArray) . "\r\n<br>");

        $y = new YaUtil();

        if(count($resultHasCall->callIdArray) > 0) {
            $loadResult = $y->sendCallCsv($resultHasCall->csv, $customerId);
        }
        if(count($resultNumberNotAcquired->callIdArray) > 0) {
            $loadNoFreeNumberResult = $y->sendNoFreeNumberCsv($resultNumberNotAcquired->csv, $customerId);
        }

        if (isset($loadResult) &&  $loadResult == true) {
            $cd = new SetCallYaUploadStateCommand(array('yaUploadState' => 0, 'callIdArray' => $resultHasCall->callIdArray));
            $r->executeTransaction($cd);
        }

        if (isset($loadNoFreeNumberResult) && $loadNoFreeNumberResult == true) {
            $cd1 = new SetEventYaUploadStateCommand(array('yaUploadState' => 0, 'eventIdArray' => $resultNumberNotAcquired->eventIdArray));
            $r->executeTransaction($cd1);
        }
    } catch
    (Exception $ex) {
        error_log($ex->getMessage());
        print("Ошибка загрузки: [" . $ex->getMessage() . "]\r\n<br>");
        return;
        // Повторный запуск в случае неудачи через паузу
    }
    print("Файл отправлен\r\n<br>");
}
return true;

