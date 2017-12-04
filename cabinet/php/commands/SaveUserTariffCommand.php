<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");

class SaveUserTariffCommand extends Command {

    private $insertNewUserTariff = "INSERT INTO customer_tariff_history(customer_id, tariff_id, set_date) VALUES(?,?, NOW())";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newTariffSet = $this->args['selectedTariff'];
        $customerUid = $this->args['customerUid'];

        $existingId = null;

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);

        if ($stmt = $conn->prepare($this->insertNewUserTariff)) {
            $updateNumberId = null;
            $customerId = null;

            $stmt->bind_param("ii", $customerId, $updateNumberId);

            $updateNumberId = $newTariffSet->tariff_id;
            $customerId = $customer->customerId;

            $stmt->execute();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_USER_TARIFF->message);
        }

        return $this->resultOK();
    }

}