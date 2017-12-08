<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/Tariff.php');

class UserTariffCommand  extends Command {

    private $userTariffSQL = "
        SELECT cth.tariff_id, t.tariff_name, t.max_phone_number, t.rate, cth.set_date
        FROM customer c
        LEFT JOIN customer_tariff_history cth on cth.customer_id = c.customer_id  
        LEFT JOIN tariff t ON t.tariff_id=cth.tariff_id
        WHERE c.customer_uid = ?
        ORDER BY cth.set_date DESC
        LIMIT 1";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $userTariff = null;

        if ($stmt = $conn->prepare($this->userTariffSQL)) {
            $row = array();
            $stmt->bind_param("s", $this->args['customerUid']);
            $stmt->bind_result($row['tariff_id'], $row['tariff_name'], $row['max_phone_number'], $row['rate'], $row['set_date']);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows() > 0 && $stmt->fetch() != false) {
                $userTariff = UserTariff::create($row);
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_USER_TARIFF_QUERY->message);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_USER_TARIFF_STATEMENT->message);
        }
        return $userTariff;
    }
}