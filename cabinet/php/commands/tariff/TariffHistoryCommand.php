<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Tariff.php');
require_once(dirname(__DIR__) . '/../domain/CustomerTariff.php');

class TariffHistoryCommand  extends Command {

    private $getAllTariffsSQL = 'select * from customer_tariff_history where customer_id = ? order by set_date asc';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $history = array();
        $item = null;
        $customerId = null;
        $tariffId = null;
        $setDate = null;

        if ($stmt = $conn->prepare($this->getAllTariffsSQL)) {
            $stmt->bind_param("i", $this->args['customerId']);
            $stmt->bind_result($customerId, $tariffId, $setDate);
            $stmt->execute();
            $stmt->store_result();
            while($stmt->fetch() != false) {
                $item = CustomerTariff::create($customerId, $tariffId, $setDate);
                array_push($history, $item);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_CUSTOMER_TARIFF_HISTORY->message);
        }
        return $history;
    }
}