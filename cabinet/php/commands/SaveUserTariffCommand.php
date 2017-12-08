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
        $selectedTariff = $this->args['selectedTariff'];
        $customerUid = $this->args['customerUid'];

        $existingId = null;

        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);

        $phoneNumberList = PhoneNumberPoolRepo::getInstance()->getPhoneNumberList($customer->customerUid)->itemArray;
        $currentTariff = TariffRepo::getInstance()->tariffById($selectedTariff);

        if(count($phoneNumberList) > $currentTariff->maxPhoneNumber) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_SAVE_USER_TARIFF_MAX_PHONE_NUMBER_TOO_SMALL->message);
        }

        if ($stmt = $conn->prepare($this->insertNewUserTariff)) {
            $updateNumberId = null;
            $customerId = null;

            $stmt->bind_param("ii", $customerId, $updateNumberId);

            $updateNumberId = $selectedTariff;
            $customerId = $customer->customerId;

            $stmt->execute();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_USER_TARIFF->message);
        }

        return $this->resultOK();
    }

}