<?php

require_once('Command.php');

class ResetForgottenPwdCommand extends Command {

    private $checkRestoreTokenSQL = 'select c.*, t.tariff_name from customer c left join tariff t on t.tariff_id = c.tariff_id where 
                                        c.customer_uid = ?  and c.reset_pwd_uid = ? 
                                        and c.timestampdiff(second, now(), c.reset_pwd_valid_till) > 0';
    private $clearRestoreTokenSQL = 'update customer set reset_pwd_uid = null, reset_pwd_valid_till = null where customer_uid = ?';
    private $updatePasswordSQL = 'update customer set hkey = ? where customer_uid = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $customer = null;
        if ($stmt = $conn->prepare($this->checkRestoreTokenSQL)) {
            $stmt->bind_param("ss", $this->args['customerUid'], $this->args['token']);

            $customer = new Customer();

            $stmt->bind_result(
                $customer->customerId,
                $customer->email,
                $customer->pwdHash,
                $customer->description,
                $customer->restore_uid,
                $customer->restore_valid_till,
                $customer->reset_pwd_uid,
                $customer->reset_pwd_valid_till,
                $customer->gaId,
                $customer->defaultNumber,
                $customer->defaultDomain,
                $customer->scriptToken,
                $customer->customerUid,
                $customer->timeZone,
                $customer->yaId,
                $customer->yaIdAuth,
                $customer->yaRefresh,
                $customer->yaExpires,
                $customer->role,
                $customer->upTimeFrom,
                $customer->upTimeTo,
                $customer->upTimeSchedule,
                $customer->tariffId,
                $customer->balance,
                $customer->tariffName
            );

            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_RESTORE->message);
        }

        if($customer == null || $customer->customerUid == null) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_SUCH_USER_OR_RESTORE_PWD_TOKEN_EXPIRED->message);
        }

        if ($stmt1 = $conn->prepare($this->clearRestoreTokenSQL)) {
            $stmt1->bind_param("s", $this->args['customerUid']);
            $stmt1->execute();
            $stmt1->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CLEAR_RESTORE_PWD_TOKEN->message);
        }

        if ($stmt1 = $conn->prepare($this->updatePasswordSQL)) {
            $h = password_hash($this->args['newPwd'], PASSWORD_DEFAULT);
            $stmt1->bind_param("ss", $h, $customer->customerUid);
            $stmt1->execute();
            $stmt1->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CHANGE_PWD->message);
        }

        return $this->resultOK();
    }

}