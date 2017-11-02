<?php

require_once(dirname(__DIR__) . '/Command.php');

class GetNoFreeNumberEventsCSVCommand extends Command {

    private $getNoFreeNumberEventsSQL = 'select co.call_object_id, co.ya_client_id, co.call_date_time, co.url 
                                          from call_object co, customer c, number_pool np 
                                          where c.customer_id = co.customer_id	and np.id = co.number_id 
                                          and c.customer_id = ? and (ya_upload != 0 or ya_upload is null) and co.type_id = -1';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $resultCSV = "ClientId,DateTime,Price,Currency,PhoneNumber,TalkDuration,HoldDuration,CallMissed,Tag,FirstTimeCaller,URL,CallTrackerURL\r\n";

        $callEventId = null;
        $clientId = null;
        $eventDateTime = null;
        $callNumber = null;
        $url = null;
        $tag = "";

        if ($stmt = $conn->prepare($this->getNoFreeNumberEventsSQL)) {
            $stmt->bind_param("s", $this->args['customerId']);
            $stmt->bind_result($callEventId, $clientId, $eventDateTime, $url);

            $eventIdArray = array();

            $stmt->store_result();
            if($stmt->num_rows() == 0) {
                $stmt->close();
                $res = new stdClass();
                $res->csv = "";
                $res->callIdArray = $eventIdArray;
                return $res;
            }

            $tmp = "";
            while($stmt->fetch() != false) {
                $tmp =  $clientId . "," .
                    strtotime($eventDateTime) . "," .
                    0 . "," .
                    0 . "," .
                    $callNumber . "," .
                    0 . "," .
                    0 . "," .
                    0 . "," .
                    $tag . "," .
                    1 . "," .
                    $url . "," .
                    AppConfig::APP_HOST;
                $resultCSV = $resultCSV . $tmp . "\r\n";
                array_push($eventIdArray, $callEventId);
            }
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_NO_FREE_NUMBER_EVENTS->message);
        }

        $res = new stdClass();
        $res->csv = $resultCSV;
        $res->eventIdArray = $eventIdArray;
        return $res;
    }

}