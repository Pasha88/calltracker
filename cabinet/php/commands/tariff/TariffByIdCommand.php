<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Tariff.php');

class TariffByIdCommand  extends Command {

    private $getAllTariffsSQL = 'select * from tariff where tariff_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $tariffId = null;
        $tariffName = null;
        $maxPhoneNumber = null;
        $rate = null;
        $isDeleted = null;

        if ($stmt = $conn->prepare($this->getAllTariffsSQL)) {
            $stmt->bind_param("i", $this->args['id']);
            $stmt->bind_result($tariffId, $tariffName, $maxPhoneNumber, $rate, $isDeleted);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->fetch() != false) {
                $tariff = Tariff::createByArg($tariffId, $tariffName, $maxPhoneNumber, $rate, $isDeleted);
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_BY_ID->message);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_BY_ID->message);
        }
        return $tariff;
    }
}