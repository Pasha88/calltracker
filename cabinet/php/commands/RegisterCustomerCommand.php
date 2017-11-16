<?php

require_once (dirname(__DIR__)."/commands/Command.php");
require_once(dirname(__DIR__) . '/util/Util.php');

class RegisterCustomerCommand  extends Command {

    private $saveCustomerSQL = 'insert into customer(email, hkey, customer_uid) values(?,?,?)';
    private $saveTariffHistorySQL = 'insert into customer_tariff_history(customer_uid, tariff_id, set_date) values(?,?,?)';
    private $emailExistSQL = 'select count(customer_id) as cnt from customer where email = ?';
    private $pwdRegexp = '/^([0-9A-Za-z_!@#$%^&.]{3,20})$/';
    private $emailRegexp = "(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {

        if(filter_var($this->args['email'], FILTER_VALIDATE_EMAIL) == false) {
            throw new Exception($this->errorRegistry->USER_ERR_EMAIL_INVALID->message);
        }

        if(preg_match($this->pwdRegexp, $this->args['hkey']) == false) {
            throw new Exception($this->errorRegistry->USER_ERR_PWD_INVALID->message);
        }

        if ($stmt = $conn->prepare($this->emailExistSQL)) {
            $stmt->bind_param("s", $this->args['email']);

            $res = 0;
            $stmt->bind_result($res);
            $stmt->execute();
            $stmt->fetch();
        }
        else {
            throw new Exception($this->errorRegistry->E001->message);
        }
        $stmt->close();

        if($res > 0) {
            throw new Exception($this->errorRegistry->USER_ERR_EMAIL_EXISTS->message);
        }

        if ($stmt = $conn->prepare($this->saveCustomerSQL)) {
            $stmt->bind_param("sss", $this->args['email'], password_hash($this->args['hkey'], PASSWORD_BCRYPT), Util::uuid());
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception($this->errorRegistry->USER_ERR_REGISTRATION->message . "; " . $this->errorRegistry->E002->message);
        }

        $id = $conn->insert_id;

        if ($stmt = $conn->prepare($this->saveTariffHistorySQL)) {
            $stmt->bind_param("sis", $id, 1, Util::getCurrentServerDateFormatted());
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception($this->errorRegistry->USER_ERR_SAVE_TARIFF_HISTORY->message . "; " . $this->errorRegistry->E002->message);
        }

        $c = new FindCustomerCommand( array( 'customerId' => $id ) );
        $customer = $c->execute($conn);

        $propRepo = AppPropertyRepo::getInstance();
        $from = $propRepo->read('MAIL_SMTP_USER');

        try {
            $subj = "Регистрация на allostat.ru";
            $body = "Вы зарегистрированы на Allostat.ru. <br> Для входа пройдите по ссылке: <a href=\"" . AppConfig::LOGIN_LINK . "\">" . AppConfig::LOGIN_LINK . "</a>";
            Util::sendEmail($from, $customer->email, $subj, $body, null, null, null);
        }
        catch(Exception $ex) {
            error_log(Util::normErrMsg($ex->getMessage()));
        }

        $token = JWTTool::createToken($customer->email, $_SERVER['HTTP_ORIGIN'], JWTTool::$expireTokenPeriodSec10Min);
        $result = new stdClass();
        $result->token = $token;
        $result->customerUid = $customer->customerUid;
        $result->customerEmail = $customer->email;
        $result->role = $customer->role;

        return $result;
    }

}