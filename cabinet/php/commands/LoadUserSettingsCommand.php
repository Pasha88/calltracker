<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class LoadUserSettingsCommand extends Command{

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $result = new stdClass();

        $c = new FindCustomerCommandByUid(array('customerUid' => $this->args['customerUid']));
        $customer = $c->execute($conn);

        $result->customerTimeZone = $customer->timeZone;
        $result->upTimeFrom = $customer->upTimeFrom;
        $result->upTimeTo = $customer->upTimeTo;
        $result->upTimeSchedule = $customer->upTimeSchedule;

        return $result;
    }

}