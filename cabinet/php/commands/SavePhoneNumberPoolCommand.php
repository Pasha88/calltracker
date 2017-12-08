<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");
require_once (dirname(__DIR__)."/repo/TariffRepo.php");
require_once (dirname(__DIR__)."/repo/CustomerRepo.php");

class SavePhoneNumberPoolCommand  extends Command {

    private $insertPhoneNumberToPoolSQL = 'insert into number_pool(customer_id, phone_number, description, free_date_time) values(?,?,?,?)';
    private $updatePhoneNumberToPoolSQL = 'update number_pool set customer_id = ?, phone_number = ?, description = ?, free_date_time = ? where id = ?';
    private $deletePhoneNumberPoolSQL = 'delete from number_pool where id = ?';
    private $getExistingIdsSQL = "select id from number_pool where customer_id = ?";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newNumberPool = $this->args['numberPool'];
        $customerUid = $this->args['customerUid'];
        $desc = "";
        $fdt = null;
        $number = "";

        $forDelete = array();
        $forUpdate = array();
        $existingId = null;

        $customer = CustomerRepo::getInstance()->getCustomerByUid($this->args['customerUid']);
        $userTariff = TariffRepo::getInstance()->getUserTarif($customer->customerUid);

        if(count($newNumberPool) > $userTariff->maxPhoneNumber) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_MAX_PHONE_NUMBER_EXCEEDED->message);
        }

        if ($stmt = $conn->prepare($this->getExistingIdsSQL)) {
            $stmt->bind_param("i", $customer->customerId);
            $stmt->bind_result($existingId);
            $stmt->execute();

            while($stmt->fetch()) {
                for($j=0; $j<count($newNumberPool); $j++) {
                    if($newNumberPool[$j]->id == $existingId) {
                        array_push($forUpdate, $newNumberPool[$j]);
                        continue 2;
                    }
                }
                array_push($forDelete, $existingId);
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);
        $customerId = $customer->customerId;

        $numberId = null;
        if ($stmt_del = $conn->prepare($this->deletePhoneNumberPoolSQL)) {
            $stmt_del->bind_param("i", $numberId);
            for($i=0; $i<count($forDelete); $i++) {
                $numberId = $forDelete[$i];
                $stmt_del->execute();
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        if ($stmt = $conn->prepare($this->insertPhoneNumberToPoolSQL)) {
            $stmt->bind_param("isss", $customerId, $number, $desc, $fdt);

            for($i=0; $i<count($this->args['numberPool']); $i++) {
                $item = $this->args['numberPool'][$i];
                if($item->id == null) {
                    $number = $item->number;
                    $fdt = Util::getCurrentDateFormatted();
                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        if ($stmt = $conn->prepare($this->updatePhoneNumberToPoolSQL)) {
            $updateNumberId = null;
            $stmt->bind_param("isssi", $customerId, $number, $desc, $fdt, $updateNumberId);

            for($i=0; $i<count($forUpdate); $i++) {
                $item = $this->args['numberPool'][$i];
                if($item->id != null) {
                    $updateNumberId = $item->id;
                    $number = $item->number;
                    $fdt = Util::getCurrentDateFormatted();
                    $desc = '';
                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        return $this->resultOK();
    }

}