<?php

require_once(dirname(__DIR__) . '/Command.php');

class NoFreeNumberEventCommand extends Command {

    private $saveNoFreeNumberEventSQL = 'insert into call_event(event_code, event_date_time, customer_uid, ya_client_id) values(?,?,?,?)';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        if ($stmt = $conn->prepare($this->saveNoFreeNumberEventSQL)) {
            $eventDate = Util::getCurrentDateFormatted();
            $eventCode = "NO_FREE_NUMBER";
            $stmt->bind_param("ssss", $eventCode, $eventDate, $this->args['customerUid'], $this->args['yaClientId']);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_FREE_NUMBER_EVENT_SAVE->message);
        }
    }

}