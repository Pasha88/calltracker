<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__) . '/domain/UserTariff.php');

class GetUserTariffCommand extends Command {

    private $getCurrentUserTariffSQL = "
        SELECT cth.tariff_id, t.tariff_name, t.max_phone_number, t.rate, cth.set_date
        FROM customer_tariff_history cth
        LEFT JOIN tariff t ON t.tariff_id=cth.tariff_id
        WHERE cth.customer_id = ?
        ORDER BY cth.set_date DESC
        LIMIT 1";

    private $getTariffsSQL = "
        SELECT tariff_id, tariff_name, max_phone_number, rate, is_deleted 
        FROM tariff WHERE is_deleted = 0
        AND  tariff_id != ?";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        if ($stmt = $conn->prepare($this->getCurrentUserTariffSQL)) {
            $customerUid = $this->args['customerUid'];
            $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
            $customer = $c->execute($conn);

            $excludeTariffId = null;
            $row = array();
            $customerId = null;
            $stmt->bind_param("i", $customerId);
            $customerId = $customer->customerId;
            $stmt->bind_result($row['tariff_id'], $row['tariff_name'], $row['max_phone_number'], $row['rate'], $row['set_date']);
            $stmt->execute();

            $i=0;
            $userTariffArray = array();
            while ($stmt->fetch())
            {
                $excludeTariffId = $row['tariff_id'];
                $userTariffArray[$i] = UserTariff::create($row);
                $i++;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_USER_TARIFF->message);
        }

        if ($stmt = $conn->prepare($this->getTariffsSQL)) {
            $row = array();
            $stmt->bind_param("i", $excludeTariffId);

            $stmt->bind_result($row['tariff_id'], $row['tariff_name'], $row['max_phone_number'],   $row['rate'], $row['is_deleted']);
            $stmt->execute();

            $i=0;
            $tariffForChangeArray = array();
            while ($stmt->fetch())
            {
                $tariffForChangeArray[$i] = Tariff::create($row);
                $i++;
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_USER_TARIFF->message);
        }

        $result = new stdClass();
        $result->userTariffArray = $userTariffArray;
        $result->tariffForChangeArray = $tariffForChangeArray;
        return $result;
    }

}