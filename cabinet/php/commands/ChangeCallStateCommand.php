<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class ChangeCallStateCommand extends Command {

    private $updateCallStateSQL = 'update call_object set type_id = ?, modify_date = ? where call_object_id = ? and type_id != ' . AppConfig::CALL_TYPE_HAS_CALL;
    private $findCallSQL = 'select call_object_id, client_id, call_date_time, type_id, description, customer_id, number_id  from call_object where call_object_id = ?';
    private $getGaIdSQL = 'select c.ga_id from customer c, number_pool np where c.customer_id = np.customer_id and np.id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $rows_affected = 0;

        if ($stmt = $conn->prepare($this->updateCallStateSQL)) {
            $d = Util::getCurrentDateFormatted();
            $stmt->bind_param("iss", $this->args['typeId'], $d, $this->args['callObjectId']);
            $stmt->execute();
            $rows_affected = $stmt->affected_rows;
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_CALL_STATE_CHANGE->message);
        }

        if($this->args['typeId'] == AppConfig::CALL_TYPE_NO_CALL) {
            return $this->resultOK();
        }

        if($this->args['typeId'] == AppConfig::CALL_TYPE_HAS_CALL && $rows_affected >= 1) {
            $row = array();
            $call = null;
            if ($stmt = $conn->prepare($this->findCallSQL)) {
                $stmt->bind_param("i", $this->args['callObjectId']);
                $stmt->bind_result($row['call_object_id'], $row['client_id'], $row['call_date_time'], $row['type_id'], $row['description'], $row['customer_id'], $row['number_id']);
                $stmt->execute();
                $stmt->fetch();
                $call = new Call($row);
                $stmt->close();
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_CALL_STATE_CHANGE->message);
            }

            $gaId = null;
            if ($stmt = $conn->prepare($this->getGaIdSQL)) {
                $stmt->bind_param("i", $call->numberId);
                $stmt->bind_result($gaId);
                $stmt->execute();
                $stmt->fetch();
                $stmt->close();
            }
            else {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_GA_ID->message);
            }
            if($gaId == null || strlen($gaId) == 0) {
                throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_GA_ID->message);
            }

            try {
                $ga = new GAUtil();
                $ga->sendEventWithMeta($gaId, $call->client_id, '', AppConfig::GA_MEASUREMENT_EVENT_CATEGORY_ALLOSTAT, AppConfig::GA_MEASUREMENT_EVENT_ACTION_HAS_CALL);
            }
            catch(Exception $ex) {

                $conn->rollback(); // Отменяем изменение статуса

                if ($stmt = $conn->prepare($this->updateCallStateSQL)) {
                    $stmt->bind_param("ii", $typeId = AppConfig::CALL_TYPE_GA_ERROR, $this->args['callObjectId']);
                    $stmt->execute();
                    $stmt->close();
                }
                else {
                    throw new Exception( $this->getErrorRegistry()->USER_ERR_CALL_STATE_CHANGE->message);
                }
                return $this->resultErr($this->getErrorRegistry()->USER_ERR_GA_SEND->message);
            }

        }
        return $this->resultOK();
    }

}