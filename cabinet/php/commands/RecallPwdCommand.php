<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__). "/AppConfig.php");

class RecallPwdCommand extends Command {

    private $getCustomerByEmailSQL = 'select c.*, t.tariff_name from customer c left join tariff t on t.tariff_id = c.tariff_id where c.email = ?';
    private $saveResoreTokenSQL = 'update customer set restore_uid = ?, restore_valid_till = ? where customer_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $customer = null;
        if ($stmt = $conn->prepare($this->getCustomerByEmailSQL)) {
            $stmt->bind_param("s", $this->args['email']);

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
            throw new Exception( $this->getErrorRegistry()->USER_ERR_FIND_CUSTOMER_BY_EMAIL->message);
        }

        if($customer == null || $customer->customerId == null) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CUSTOMER_NOT_EXISTS->message);
        }

        $restoreToken = md5(uniqid($this->args['email'], true));

        if ($stmt1 = $conn->prepare($this->saveResoreTokenSQL)) {
            $validTillDateTime = Util::getCurrentDate();
            $propRepo = AppPropertyRepo::getInstance();
            $di = new DateInterval('PT' . $propRepo->read('RESTORE_TOKEN_EXPIRE_INTERVAL_SEC') . 'S');
            $validTillDateTime->add($di);

            $stmt1->bind_param("sss", $restoreToken, $validTillDateTime->format(Util::COMMON_DATE_TIME_FORMAT), $customer->customerUid);
            $stmt1->execute();
            $stmt1->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_SAVE_RESTORE_TOKEN->message);
        }

        $propRepo = AppPropertyRepo::getInstance();
        $from = $propRepo->read('MAIL_SMTP_USER');

        $restore_link = AppConfig::HOST_PROTO_PREFIX . $_SERVER['HTTP_HOST'] . "/public_api/restorepwd?recallUID=" . $restoreToken. "&customerUid=" . $customer->customerUid;
        $subj = 'Восстановление пароля на allostat.ru';
        $body = "Для восстановления пароля пройдите по ссылке или вставьте ее в адресную строку браузера: <br> Для входа пройдите по ссылке: <a href=\"" . $restore_link . "\">" . $restore_link . "</a>";
        Util::sendEmail($from, $customer->email, $subj, $body, null, 'no-reply@allostat.ru', 'no-reply@allostat.ru');

        return $this->resultOK();
    }
}