<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Tariff.php');

class TariffListCommand  extends Command {

    private $getAllTariffsSQL = 'select * from tariff';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $tariffs = [];
        $tariffId = null;
        $tariffName = null;
        $maxPhoneNumber = null;
        $rate = null;

        if ($stmt = $conn->prepare($this->getAllTariffsSQL)) {
            $stmt->bind_result($tariffId, $tariffName, $maxPhoneNumber, $rate);
            $stmt->execute();
            while($stmt->fetch() != false) {
                $tariff = new Tariff($tariffId, $tariffName, $maxPhoneNumber, $rate);
                array_push($tariffs, $tariff);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_ALL_TARIFFS->message);
        }
        return $tariffs;
    }
}