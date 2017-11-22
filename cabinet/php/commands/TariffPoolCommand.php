<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");

class TariffPoolCommand extends Command {

    private $insertTariffToPoolSQL = "INSERT INTO tariff(tariff_name, max_phone_number, rate, is_deleted) VALUES(?,?,?,?)";
    private $updateTariffToPoolSQL = "UPDATE tariff SET tariff_name = ?, max_phone_number = ?, rate = ?, is_deleted = ? WHERE tariff_id = ?";
    private $deleteTariffPoolSQL = "UPDATE tariff SET is_deleted = ? WHERE tariff_id = ?";
    private $getExistingIdsSQL = "SELECT tariff_id FROM tariff";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newNumberPool = $this->args['numberPool'];
        $customerUid = $this->args['customerUid'];

        $isDeleted = 0;
        $forDelete = array();
        $forUpdate = array();
        $existingId = null;

        if ($stmt = $conn->prepare($this->getExistingIdsSQL)) {
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
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        $idsToDelete = null;
        if ($stmt_del = $conn->prepare($this->deleteTariffPoolSQL)) {
            $isDeleted = 1;
            $idsToDelete = null;
            $stmt_del->bind_param("ii", $isDeleted, $idsToDelete);
            for($i=0; $i<count($forDelete); $i++) {
                $idsToDelete = $forDelete[$i];
                $stmt_del->execute();
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        $isDeleted = 0;

        if ($stmt = $conn->prepare($this->insertTariffToPoolSQL)) {
            $tariffName = null;
            $maxPhoneNumber = null;
            $rate = null;

            $stmt->bind_param("sidi", $tariffName, $maxPhoneNumber, $rate, $isDeleted);

            for($i=0; $i<count($this->args['numberPool']); $i++) {
                $item = $this->args['numberPool'][$i];
                if(empty($item->tariff_id)) {
                    $tariffName = $item->tariff_name;
                    $maxPhoneNumber = $item->max_phone_number;
                    $rate = $item->rate;
                    $fdt = Util::getCurrentDateFormatted();
                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        if ($stmt = $conn->prepare($this->updateTariffToPoolSQL)) {
            $tariffName = null;
            $maxPhoneNumber = null;
            $rate = null;
            $updateNumberId = null;
            $stmt->bind_param("sidii", $tariffName, $maxPhoneNumber, $rate, $isDeleted, $updateNumberId);

            for($i=0; $i<count($forUpdate); $i++) {
                $item = $this->args['numberPool'][$i];
                if(!empty($item->tariff_id)) {
                    $updateNumberId = $item->tariff_id;
                    $tariffName = $item->tariff_name;
                    $maxPhoneNumber = $item->max_phone_number;
                    $rate = $item->rate;
                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        return $this->resultOK();
    }

}