<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class CreateRequestCommand extends Command {

    private $createSupportRequestSQL = 'insert into support_request(customer_id, request_content, request_status_id, create_date_time) values(?,?,?,?)';
    private $bindFilesSQL = 'update file_object set request_id=? where file_id = ?';
    private $getFileSQL = 'select filename, file_content from file_object where request_id=?';
//    private $getCustomerSQL = 'select * from customer where customer_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);
        $customerId = $customer->customerId;

        if ($stmt = $conn->prepare($this->createSupportRequestSQL)) {
            $stmt->bind_param("isis", $customerId, $this->args['requestText'], $statusId = 1, Util::getCurrentDateFormatted());
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_CREATE_SUPPORT_REQUEST->message);
        }

        $request_id = $conn->insert_id;

        if($request_id <= 0) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_CREATE_SUPPORT_REQUEST->message);
        }

        $fileIdList = $this->args['fileArray'];
        if ($stmt = $conn->prepare($this->bindFilesSQL)) {
            for($i=0; $i<count($fileIdList); $i++) {
                $stmt->bind_param("ii", $request_id, $fileIdList[$i]);
                $res = $stmt->execute();
                if($res == false) {
                    throw new Exception($this->getErrorRegistry()->USER_ERR_BIND_SUPPORT_REQUEST_FILES->message);
                }
            }
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_BIND_SUPPORT_REQUEST_FILES->message);
        }


        $attachments = array();
        $fileName_res = null;
        $fileContent_res = null;
        if ($stmt = $conn->prepare($this->getFileSQL)) {
            $stmt->bind_param("i", $request_id);
            $stmt->bind_result($fileName_res, $fileContent_res);
            $res = $stmt->execute();
            if($res == false) {
                throw new Exception($this->getErrorRegistry()->USER_ERR_GET_FILE_CONTENT->message);
            }
            while($stmt->fetch() != false) {
                array_push($attachments, array('name' => $fileName_res, 'content' => $fileContent_res));
            }
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_BIND_SUPPORT_REQUEST_FILES->message);
        }

        $propRepo = AppPropertyRepo::getInstance();
        $from = $propRepo->read('MAIL_SMTP_USER');

        $subj = 'Обращение пользователя № [' . $request_id . ']';
        $body = $this->args['requestText'];
        $sendResult = Util::sendEmail($from, $from, $subj, $body, $attachments, $customer->email, $customer->email);
        if($sendResult == false) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_CREATE_SUPPORT_REQUEST->message);
        }

        $body2 = "Добрый день. По вашему обращению была зарегистрирована заявка № [" . $request_id . "]<br> Вы писали: \" $body \" ";
        $sendResult = Util::sendEmail($from, $customer->email, $subj, $body2, $attachments, $from, $from);
        if($sendResult == false) {
            throw new Exception($this->getErrorRegistry()->USER_ERR_CREATE_SUPPORT_REQUEST->message);
        }

        $result = new stdClass();
        $result->requestId = $request_id;

        return $result;
    }
}