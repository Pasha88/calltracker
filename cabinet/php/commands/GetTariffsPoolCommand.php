<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__) . '/domain/Tariff.php');

class GetTariffsPoolCommand extends Command {

    private $getTariffPoolSQL = "SELECT tariff_id, tariff_name, max_phone_number, rate, is_deleted FROM tariff WHERE is_deleted = 0";
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {

        if ($stmt = $conn->prepare($this->getTariffPoolSQL)) {
            $row = array();
            $stmt->bind_result($row['tariff_id'], $row['tariff_name'], $row['max_phone_number'],   $row['rate'], $row['is_deleted']);
            $stmt->execute();

            $i=0;
            $resultArray = array();
            while ($stmt->fetch())
            {
                $resultArray[$i] = Tariff::create($row);
                $i++;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_TARIFF_POOL->message);
        }

        $result = new stdClass();
        $result->itemArray = $resultArray;
        return $result;
    }

}