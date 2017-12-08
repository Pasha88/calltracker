<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");

class TariffCommand extends Command {

    private $insertTariffSetSQL = "INSERT INTO tariff(tariff_name, max_phone_number, rate, is_deleted) VALUES(?,?,?,?)";
    private $updateTariffSetSQL = "UPDATE tariff SET tariff_name = ?, max_phone_number = ?, rate = ?, is_deleted = ? WHERE tariff_id = ?";
    private $deleteTariffSetSQL = "UPDATE tariff SET is_deleted = ? WHERE tariff_id = ?";
    private $getExistingIdsSQL = "SELECT tariff_id FROM tariff";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newTariffSet = $this->args['tariffSet'];

        $isDeleted = 0;
        $forDelete = array();
        $forUpdate = array();
        $existingId = null;

        if ($stmt = $conn->prepare($this->getExistingIdsSQL)) {
            $stmt->bind_result($existingId);
            $stmt->execute();

            while($stmt->fetch()) {
                for($j=0; $j<count($newTariffSet); $j++) {
                    if(!empty($newTariffSet[$j]->tariffId)) {
                        if($newTariffSet[$j]->tariffId == $existingId) {
                            array_push($forUpdate, $newTariffSet[$j]);
                            continue 2;
                        }
                    }

                }
                array_push($forDelete, $existingId);
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        $idsToDelete = null;
        if ($stmt_del = $conn->prepare($this->deleteTariffSetSQL)) {
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

        if ($stmt = $conn->prepare($this->insertTariffSetSQL)) {
            $tariffName = null;
            $maxPhoneNumber = null;
            $rate = null;

            $stmt->bind_param("sidi", $tariffName, $maxPhoneNumber, $rate, $isDeleted);

            for($i=0; $i<count($this->args['tariffSet']); $i++) {
                $item = $this->args['tariffSet'][$i];
                if(empty($item->tariffId)) {
                    $tariffName = $item->tariffMame;
                    $maxPhoneNumber = $item->maxPhoneNumber;
                    $rate = $item->rate;

                    $stmt->execute();
                }
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        if ($stmt = $conn->prepare($this->updateTariffSetSQL)) {
            $tariffName = null;
            $maxPhoneNumber = null;
            $rate = null;
            $updateNumberId = null;
            $stmt->bind_param("sidii", $tariffName, $maxPhoneNumber, $rate, $isDeleted, $updateNumberId);

            for($i=0; $i<count($forUpdate); $i++) {
                $item = $this->args['tariffSet'][$i];
                if(!empty($item->tariffId)) {
                    $updateNumberId = $item->tariffId;
                    $tariffName = $item->tariffName;
                    $maxPhoneNumber = $item->maxPhoneNumber;
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