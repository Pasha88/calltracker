<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Tariff.php');

class SaveTariffListCommand  extends Command {

    private $updateTariffSQL = 'update tariff set tariff_name = ?, max_phone_number = ?, rate = ? where tariff_id = ?';
    private $insertTariffSQL = 'insert into tariff values(?,?,?,?)';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {

        $tariffId = null;
        $tariffName = null;
        $maxNumberCount = null;
        $rate = null;

        if ($stmt = $conn->prepare($this->updateTariffSQL)) {
            $stmt->bind_param("sids", $tariffName, $maxNumberCount, $rate, $tariffId);
            foreach($this->args['tariffList'] as $tariff) {
                if(isset($tariff['tariff_id'])) {
                    $tariffId = $tariff['tariff_id'];
                    $tariffName = $tariff['tariff_name'];
                    $maxNumberCount = $tariff['max_number_count'];
                    $rate = $tariff['rate'];
                }
                $stmt->execute();
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_UPDATE_TARIFF->message);
        }

        $tariffId = null;
        $tariffName = null;
        $maxNumberCount = null;
        $rate = null;

        if ($stmt = $conn->prepare($this->insertTariffSQL)) {
            $stmt->bind_param("ssid", $tariffId, $tariffName, $maxNumberCount, $rate);
            foreach($this->args['tariffList'] as $tariff) {
                if(!isset($tariff['tariff_id'])) {
                    $tariffId = $tariff['tariff_id'];
                    $tariffName = $tariff['tariff_name'];
                    $maxNumberCount = $tariff['max_number_count'];
                    $rate = $tariff['rate'];
                }
                $stmt->execute();
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_INSERT_TARIFF->message);
        }
    }
}