<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");

class TariffPoolCommand extends Command {

    private $insertTariffToPoolSQL = 'INSERT INTO tariff(tariff_name, max_phone_number, is_deleted) VALUES(?,?,?)';
    private $updateTariffToPoolSQL = 'UPDATE tariff set max_phone_number = ?, rate = ? where tariff_id = ?';
    private $deleteTariffPoolSQL = 'DELETE FROM tariff WHERE tariff_id = ?';
    private $getExistingIdsSQL = "SELECT tariff_id FROM tariff";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newNumberPool = $this->args['numberPool'];
        $customerUid = $this->args['customerUid'];
      /*  print_r($newNumberPool);

        echo PHP_EOL;
        die;*/
        $fdt = null;
        $number = "";
        $isDeleted = 0;
        $forDelete = array();
        $forUpdate = array();
        $existingId = null;

/*        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);*/

        if ($stmt = $conn->prepare($this->getExistingIdsSQL)) {
            //$stmt->bind_param("i", $customer->customerId);
            $stmt->bind_result($existingId);
            $stmt->execute();

            while($stmt->fetch()) {
                for($j=0; $j<count($newNumberPool); $j++) {
                    if($newNumberPool[$j]->tariff_id == $existingId) {
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

        $numberId3 = null;
        if ($stmt_del = $conn->prepare($this->deleteTariffPoolSQL)) {
            $numberId = 3;
            $stmt_del->bind_param("i", $numberId3);
            for($i=0; $i<count($forDelete); $i++) {
                $numberId3 = $forDelete[$i];
                $stmt_del->execute();
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        if ($stmt = $conn->prepare($this->insertTariffToPoolSQL)) {
            $stmt->bind_param("iii", $number, $number, $isDeleted);

            for($i=0; $i<count($this->args['numberPool']); $i++) {
                $item = $this->args['numberPool'][$i];
                if(empty($item->tariff_id)) {
                    $number = $item->max_phone_number;
                    $fdt = Util::getCurrentDateFormatted();
                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        if ($stmt = $conn->prepare($this->updateTariffToPoolSQL)) {
            $updateNumberId = null;
            $stmt->bind_param("isi", $number, $number, $updateNumberId);


            for($i=0; $i<count($forUpdate); $i++) {
                $item = $this->args['numberPool'][$i];
                if(!empty($item->tariff_id)) {
                    $updateNumberId = $item->tariff_id;
                    $number = $item->max_phone_number;
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