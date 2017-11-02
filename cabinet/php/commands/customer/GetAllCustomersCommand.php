<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Customer.php');

class GetAllCustomersCommand  extends Command {

    private $getAllCustomersSQL = 'select * from customer';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $customers = [];
        $customerId = null;
        $email = null;
        $pwdHash = null;
        $description = null;
        $restore_uid = null;
        $restore_valid_till = null;
        $reset_pwd_uid = null;
        $reset_pwd_valid_till = null;
        $gaId = null;
        $defaultNumber = null;
        $defaultDomain = null;
        $scriptToken = null;
        $customerUid = null;
        $timeZone = null;
        $yaId = null;
        $yaIdAuth = null;
        $yaRefresh = null;
        $yaExpires = null;
        $role = null;
        $upTimeFrom = null;
        $upTimeTo = null;
        $upTimeSchedule = null;
        $tariffId = null;
        $tariffName = null;
        $balance = null;

        if ($stmt = $conn->prepare($this->getAllCustomersSQL)) {
            $stmt->bind_result($customerId,$email,$pwdHash,$description,$restore_uid,$restore_valid_till,$reset_pwd_uid,
                $reset_pwd_valid_till,$gaId,$defaultNumber,$defaultDomain,$scriptToken,$customerUid,$timeZone,
                $yaId,$yaIdAuth,$yaRefresh,$yaExpires,$role,$upTimeFrom,$upTimeTo,$upTimeSchedule, $tariffId, $tariffName, $balance);
            $stmt->execute();
            while($stmt->fetch() != false) {
                $customer = new Customer();
                $customer->customerId = $customerId;
                $customer->email = $email;
                $customer->pwdHash = $pwdHash;
                $customer->description = $description;
                $customer->restore_uid = $restore_uid;
                $customer->restore_valid_till = $restore_valid_till;
                $customer->reset_pwd_uid = $reset_pwd_uid;
                $customer->reset_pwd_valid_till = $reset_pwd_valid_till;
                $customer->gaId = $gaId;
                $customer->defaultNumber = $defaultNumber;
                $customer->defaultDomain = $defaultDomain;
                $customer->scriptToken = $scriptToken;
                $customer->customerUid = $customerUid;
                $customer->timeZone = $timeZone;
                $customer->yaId = $yaId;
                $customer->yaIdAuth = $yaIdAuth;
                $customer->yaRefresh = $yaRefresh;
                $customer->yaExpires = $yaExpires;
                $customer->role = $role;
                $customer->upTimeFrom = $upTimeFrom;
                $customer->upTimeTo = $upTimeTo;
                $customer->upTimeSchedule = $upTimeSchedule;
                $customer->tariffId = $tariffId;
                $customer->balance = $balance;
                $customer->tariffName = $tariffName;
                array_push($customers, $customer);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_ALL_CUSTOMERS->message);
        }
        return $customers;
    }
}