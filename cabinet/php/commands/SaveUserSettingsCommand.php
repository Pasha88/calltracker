<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class SaveUserSettingsCommand extends Command{

    private $updateCustomerSQL = 'update customer set time_zone = ?, up_time_from = ?, up_time_to = ?, up_time_schedule = ? where customer_uid = ?';

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->updateCustomerSQL)) {
            $stmt->bind_param("iiiis", $this->args['customerTimeZone'], $this->args['upTimeFrom'], $this->args['upTimeTo'], $this->args['upTimeSchedule'], $this->args['customerUid']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_SETTINGS->message);
        }

        return true;
    }

}