<?php

require_once(dirname(__DIR__) . '/php/util/Repository.php');
require_once(dirname(__DIR__) . '/php/commands/customer/GetAllCustomersCommand.php');
require_once(dirname(__DIR__) . '/php/util/Util.php');
require_once(dirname(__DIR__) . '/php/repo/CustomerRepo.php');
require_once(dirname(__DIR__) . '/php/repo/BillingRepo.php');

$r = new Repository();
$brepo = new BillingRepo();
$customerId = isset($_GET['id']) ? intval($_GET['id']) : null;

if(isset($customerId)) {
    $repo = CustomerRepo::getInstance();
    $customer = $repo->getCustomer($customerId);
    print("-------------------------- CUSTOMER: [" . $customer->email. "] ---------------------------------- \r\n<br>");
    $response = $brepo->dailyMove($customer);
    print($response . "\r\n<br>");
    return;
}

$c1 = new GetAllCustomersCommand(array());
$customers = $r->executeTransaction($c1);

foreach ($customers as $customer) {
    print("-------------------------- CUSTOMER [" . $customer->email. "]----------------------------\r\n<br>");
    $response = "";
    $response = $brepo->dailyMove($customer);
    print($response . "\r\n<br>");
}

print("--------------------------------------");
