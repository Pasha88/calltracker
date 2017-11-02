<?php

require_once ('Command.php');

class RestorePwdCommand extends Command {

    private $checkRestoreTokenSQL = 'select c.*, t.tariff_name from customer c left join tariff t on t.tariff_id = c.tariff_id where  
                                        c.customer_id = ?  and c.restore_uid = ? and c.timestampdiff(second, now(), c.restore_valid_till) > 0';
    private $clearRestoreTokenSQL = 'update customer set restore_uid = null, restore_valid_till = null where customer_uid = ?';
    private $saveResetPwdTokenSQL = 'update customer set reset_pwd_uid = ?, reset_pwd_valid_till = ? where customer_uid = ?';
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
            throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_SUCH_USER_OR_RESTORE_EXPIRED->message);
        }

        if ($stmt1 = $conn->prepare($this->clearRestoreTokenSQL)) {
            $stmt1->bind_param("s", $this->args['customerUid']);
            $stmt1->execute();
            $stmt1->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CLEAR_RESTORE_TOKEN->message);
        }

        $restoreToken = md5(uniqid($customer->email, true));

        if ($stmt2 = $conn->prepare($this->saveResetPwdTokenSQL)) {
            $validTillDateTime = Util::getCurrentDate();
            $propRepo = AppPropertyRepo::getInstance();
            $di = new DateInterval('PT' . $propRepo->read('RESTORE_PWD_TOKEN_EXPIRE_INTERVAL_SEC') . 'S');
            $validTillDateTime->add($di);

            $stmt2->bind_param("sss", $restoreToken, $validTillDateTime->format(Util::COMMON_DATE_TIME_FORMAT), $customer->customerUid);
            $stmt2->execute();
            $stmt2->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_RESET_PWD_TOKEN->message);
        }

        header("Location: /cabinet/#/reset_password?resetUID=" . $restoreToken . "&customerUid=" . $customer->customerUid, true, 303);
        $conn->commit();
        die();
    }
}