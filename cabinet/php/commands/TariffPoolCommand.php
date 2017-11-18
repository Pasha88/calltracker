<?php

require_once (dirname(__DIR__)."/util/Util.php");
require_once (dirname(__DIR__)."/commands/Command.php");

class TariffPoolCommand extends Command {

    private $insertPhoneNumberToPoolSQL = 'INSERT INTO tariff(tariff_name, max_phone_number, is_deleted) VALUES(\'bad\',3,1)';
    private $updatePhoneNumberToPoolSQL = 'update tariff set max_phone_number = ?, rate = ? where tariff_id = ?';
    private $deletePhoneNumberPoolSQL = '';
    private $getExistingIdsSQL = "select 1 from tariff";

    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn) {
        $newNumberPool = $this->args['numberPool'];
        $customerUid = $this->args['customerUid'];
      /*  print_r($newNumberPool);

        echo PHP_EOL;
        die;*/
        $fdt = null;
        $number = "";
        $seven = $newNumberPool[0]->max_phone_number;
        $one = 1;

        $forDelete = array();
        $forUpdate = array();
        $existingId = 1;

/*        $c = new FindCustomerCommandByUid( array( 'customerUid' => $this->args['customerUid'] ) );
        $customer = $c->execute($conn);*/

        if ($stmt = $conn->prepare($this->getExistingIdsSQL)) {
            //$stmt->bind_param("i", $customer->customerId);
            $stmt->bind_result($existingId);
            $stmt->execute();

            while($stmt->fetch()) {
                for($j=0; $j<count($newNumberPool); $j++) {
                    if($newNumberPool[$j]->tariff_id == $existingId) {
                        array_push($forUpdate, $newNumberPool[$j]);
                        continue 2;
                    }
                }
                array_push($forDelete, $existingId);
            }
        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        if ($stmt = $conn->prepare($this->updatePhoneNumberToPoolSQL)) {
            $updateNumberId = null;
            $stmt->bind_param("isi", $seven, $seven, $updateNumberId);


            for($i=0; $i<count($forUpdate); $i++) {
                $item = $this->args['numberPool'][$i];
                if($item->tariff_id != null) {
                    $updateNumberId = $item->tariff_id;
                    $number = $item->max_phone_number;
                    $fdt = Util::getCurrentDateFormatted();
                    $desc = '';
                    $stmt->execute();
                }
            }


        }
        else {
            throw new Exception( $this->getErrorRegistry()->USER_ERR_GET_PHONE_NUMBER_POOL->message);
        }

        return $this->resultOK();
    }

}