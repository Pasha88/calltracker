<?php

require_once (dirname(__DIR__)."/commands/Command.php");

class GetAdMetadataCommand extends Command {

    private $getGaIdSQL = 'select c.ga_id, c.ym_id from customer c, number_pool np where c.customer_id = np.customer_id and np.id = ?';
    private $getCidSQL = 'select client_id from call_object where  call_object_id = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $gaId = null;
        $ymId = null;
        if ($stmt = $conn->prepare($this->getGaIdSQL)) {
            $stmt->bind_param("i", $this->args['numberId']);
            $stmt->bind_result($gaId, $ymId);
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

        $cid = null;
        if ($stmt = $conn->prepare($this->getCidSQL)) {
            $stmt->bind_param("i", $this->args['callObjectId']);
            $stmt->bind_result($cid);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_CID->message);
        }

        if($cid == null || strlen($cid) == 0) {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_NO_CID_ID->message);
        }

        $result = new StdClass();
        $result->gaId = $gaId;
        $result->cid = $cid;
        $result->ymId = $ymId;
        return $result;
    }

}