<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class UpdateCustomerPwdCommand extends Command {

    private $updatePasswordSQL = 'update customer set hkey = ? where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);

        if(!password_verify($this->args['oldPwd'], $customer->pwdHash)) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_PASSWORD_ERROR->message);
        }

        if ($stmt1 = $conn->prepare($this->updatePasswordSQL)) {
            $h = password_hash($this->args['newPwd'], PASSWORD_DEFAULT);
            $stmt1->bind_param("ss", $h, $this->args['customerUid']);
            $stmt1->execute();
            $stmt1->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CHANGE_PWD->message);
        }

        return $this->resultOK();
    }

}