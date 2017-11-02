<?php

require_once(dirname(__DIR__) . '/Command.php');
require_once(dirname(__DIR__) . '/../domain/CallType.php');

class GetYaCallsCSVCommand extends Command {

    private $getUnsentCallsSQL = "select co.call_object_id, co.ya_client_id, co.call_date_time, np.phone_number, co.url 
                                  from call_object co, customer c, number_pool np 
                                  where c.customer_id = co.customer_id	and np.id = co.number_id 
                                  and c.customer_id = ? and co.type_id = 2 and (ya_upload != 0 or ya_upload is null)";
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $resultCSV = "ClientId,DateTime,Price,Currency,PhoneNumber,TalkDuration,HoldDuration,CallMissed,Tag,FirstTimeCaller,URL,CallTrackerURL\r\n";

        $callObjectId = null;
        $clientId = null;
        $callDateTime = null;
        $callNumber = null;
        $url = null;
        $tag = "";

        if ($stmt = $conn->prepare($this->getUnsentCallsSQL)) {
            $stmt->bind_param("i", $this->args['customerId']);
            $stmt->bind_result($callObjectId, $clientId, $callDateTime, $callNumber, $url);
            $stmt->execute();

            $callIdArray = array();

            $stmt->store_result();
            if($stmt->num_rows() == 0) {
                $stmt->close();
                $res = new stdClass();
                $res->csv = "";
                $res->callIdArray = $callIdArray;
                return $res;
            }

            $tmp = "";
            while($stmt->fetch() != false) {
                $clientId = isset($clientId) && strlen(trim($clientId)) > 0 ? $clientId : "0";
                $tmp =  $clientId . "," .
                        strtotime($callDateTime) . "," .
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
                array_push($callIdArray, $callObjectId);
            }

            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_YA_CALLS_BY_CUSTOMER->message);
        }

        $res = new stdClass();
        $res->csv = $resultCSV;
        $res->callIdArray = $callIdArray;
        return $res;
    }

}