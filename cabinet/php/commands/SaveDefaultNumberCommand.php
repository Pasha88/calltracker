<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class SaveDefaultNumberCommand extends Command {


    private $saveDefaultNumberSQL = 'update customer set default_phone_number = ?, default_domain = ?, script_token = ? where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);

        $token = $customer->scriptToken;
        $token = JWTTool::createToken($customer->email, $this->args['domain'], JWTTool::$expireTokenPeriodSecYear);


        if ($stmt = $conn->prepare($this->saveDefaultNumberSQL)) {
            $stmt->bind_param("ssss", $this->args['number'], $this->args['domain'], $token, $this->args['customerUid']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_DEFAULT_NUMBER_SAVE->message);
        }
    }
}