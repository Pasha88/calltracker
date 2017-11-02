<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__)."/domain/Customer.php");
require_once(dirname(__DIR__)."/domain/PhoneNumber.php");
require_once(dirname(__DIR__). "/AppConfig.php");
require_once(dirname(__DIR__)."/util/GAUtil.php");
require_once(dirname(__DIR__)."/domain/CallType.php");
require_once(dirname(__DIR__) . "/repo/AppPropertyRepo.php");
require_once(dirname(__DIR__) . "/commands/yametrika/NoFreeNumberEventCommand.php");
require_once(dirname(__DIR__) . '/util/Util.php');
require_once(dirname(__DIR__) . '/repo/CustomerRepo.php');


class OccupyPhoneNumberPoolCommand extends Command {


    private $getNumberForProlongSQL = 'select id, customer_id, phone_number, description, free_date_time from number_pool where customer_id = ? and id = ? and timestampdiff(second, free_date_time, now()) < 0 order by id';
    private $getFreeNumberSQL = 'select id, customer_id, phone_number, description, free_date_time from number_pool where customer_id = ? and timestampdiff(second, free_date_time, now()) > 0 order by id';
    private $occupyPhoneNumberSQL = 'update number_pool set free_date_time = ? where id = ?';
    private $saveCallSQL = 'insert into call_object(client_id, call_date_time, type_id, customer_id, number_id, ya_client_id, url) values(?, ?, ?, ?, ?, ?, ?)';
    private $getCustomerGaIdSQL = 'select ga_id from customer where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $clientId = $this->args['clientId'];
        $phoneNumberId = $this->args['phoneNumberId'];

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);
        $customerId = $customer->customerId;

        if($customer->upTimeSchedule == 1) {
            $dt = Util::getCurrentDate();
            $dt->setDate(1970, 1, 1);
            $currentTimeSec = $dt->getTimeStamp();
            if($currentTimeSec < $customer->upTimeFrom || $currentTimeSec > $customer->upTimeTo) {
                $freeNumber = PhoneNumber::create(null);
                $freeNumber->description = "Request out of your schedule";
                return $freeNumber;
            }
        }

        if(isset($customer->yaId) && isset($this->args['yaId']) && strlen(trim($this->args['yaId']) != null)
                        && $customer->yaId != trim($this->args['yaId'])) {
            error_log("YaCounter ID on client side differs from ID stored in DB [--- ". Util::getCurrentDateFormatted() . " --- yaId->"
                . $customer->yaId . " yaId from client->" . $this->args['yaId'] . " ID->" . $customer->customerId . "---]");
            $customer->yaIdAuth = AppConfig::YA_TOKEN_NOT_VALID;
            $repo = CustomerRepo::getInstance();
            $repo->saveCustomer($customer);
        }

        $freeNumber = PhoneNumber::create(null);
        if ($stmt = $conn->prepare($this->getNumberForProlongSQL)) {
            $stmt->bind_param("ii", $customerId, $phoneNumberId);

            $row = array();
            $stmt->bind_result($row['id'], $row['customer_id'], $row['phone_number'], $row['description'], $row['free_date_time']);
            $stmt->execute();
            $stmt->fetch();
            if($row == null || $row['id'] == null) {
                if ($stmt = $conn->prepare($this->getFreeNumberSQL)) {
                    $stmt->bind_param("i", $customerId);
                    $row = array();
                    $stmt->bind_result($row['id'], $row['customer_id'],  $row['phone_number'], $row['description'], $row['free_date_time']);
                    $stmt->execute();
                    $stmt->fetch();
                }
                else {
                    throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_FREE_PHONE_NUMBER->message);
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_FREE_PHONE_NUMBER->message);
        }

        if($row != null && $row['id'] != null) {
            $freeNumber = PhoneNumber::create($row);
            $stmt->close();

            $propRepo = AppPropertyRepo::getInstance();

            if ($stmt1 = $conn->prepare($this->occupyPhoneNumberSQL)) {
                $freeDateTime = Util::getCurrentDate();
                $di = new DateInterval('PT' . $propRepo->read('PHONE_NUMBER_BUSY_INTERVAL_SEC') . 'S');
                $freeDateTime->add($di);

                $freeDateStr = $freeDateTime->format(Util::COMMON_DATE_TIME_FORMAT);
                $stmt1->bind_param("si", $freeDateStr, $freeNumber->id);
                $stmt1->execute();
                $stmt1->close();
            }
            else {
                $stmt1->close();
                throw new Exception( $this->getErrorRegistry()->USER_ERR_OCCUPY_PHONE_NUMBER->message);
            }
        }
        else {
            try {
                $call_date_time = Util::getCurrentDateFormatted();
                $typeId = CallType::NO_FREE_NUMBER;
                $numberId = null;
                $yaClientId = $this->args['yaClientId'];
                $url = $this->args['url'];

                if ($stmt2 = $conn->prepare($this->saveCallSQL)) {
                    $stmt2->bind_param("ssiiiss", $clientId, $call_date_time, $typeId, $customerId, $numberId, $yaClientId, $url);
                    $stmt2->execute();
                    $stmt2->close();
                }
                else {
                    throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_CALL->message);
                }
                if($phoneNumberId == null || strlen($phoneNumberId) == 0) {
                    $ga = new GAUtil();
                    $ga->sendEventWithMeta($customer->gaId, $clientId, $this->args['url'], AppConfig::GA_MEASUREMENT_EVENT_CATEGORY_ALLOSTAT, AppConfig::GA_MEASUREMENT_EVENT_ACTION_NO_FREE_NUMBER);
                }
            } catch(Exception $ex) {
                error_log(Util::normErrMsg($ex->getMessage()));
            }
            return $freeNumber;
//            throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_FREE_PHONE_NUMBER->message);
        }

        $numberId = $freeNumber->id;
        $typeId = CallType::INITIAL;
        $call_date_time = Util::getCurrentDateFormatted();
        $yaClientId = $this->args['yaClientId'];
        $url = $this->args['url'];
        if($phoneNumberId == null || strlen($phoneNumberId) == 0) {
            if ($stmt2 = $conn->prepare($this->saveCallSQL)) {
                $stmt2->bind_param("ssiiiss", $clientId, $call_date_time, $typeId, $customerId, $numberId, $yaClientId, $url);
                $stmt2->execute();
                $stmt2->close();
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_CALL->message);
            }

            try {
                $gaId = null;
                if ($stmt = $conn->prepare($this->getCustomerGaIdSQL)) {
                    $stmt->bind_param("s", $customer->customerUid);

                    $stmt->bind_result($gaId);
                    $stmt->execute();
                    $stmt->fetch();
                    $stmt->close();
                }
                else {
                    error_log(Util::normErrMsg($this->getErrorRegistry()->USER_ERR_GET_GA_ID->message), 0);
                }

                $ga = new GAUtil();
                $ga->sendEventWithMeta($gaId, $clientId, "", AppConfig::GA_MEASUREMENT_EVENT_CATEGORY_ALLOSTAT, AppConfig::GA_MEASUREMENT_EVENT_ACTION_NUMBER_ISSUED);
            }
            catch(Exception $ex) {
                error_log(Util::normErrMsg($this->getErrorRegistry()->USER_ERR_GA_SEND->message), 0);
            }
        }

        return $freeNumber;
    }

}