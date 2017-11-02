<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class CheckInstallCommand extends Command{

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $result = new stdClass();

        $c = new GetPhoneNumberPoolCommand(array('customerUid' => $this->args['customerUid']));
        $customerPool = $c->execute($conn);

        $result->phoneNumbers = isset($customerPool->itemArray) && count($customerPool->itemArray) >0;
        $result->defaultDomain = isset($customerPool->defaultDomain);
        $result->gaId = isset($customerPool->gaId);

        return $result;
    }

}