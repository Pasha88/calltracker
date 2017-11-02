<?php

require_once(dirname(__DIR__) . '/Command.php');

class ReadAllPropertiesCommand extends Command {

    private $readAllPropertiesSQL = 'select name, visible_name, value from application_property order by id';
    private $args;

    function __construct() {
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->readAllPropertiesSQL)) {
            $result = array();
            $propertyName = null;
            $propertyVisibleName = null;
            $propertyValue = null;

            $stmt->bind_result($propertyName, $propertyVisibleName, $propertyValue);
            $stmt->execute();

            while($stmt->fetch() != false) {
                $p = new stdClass();
                $p->name = $propertyName;
                $p->visibleName = $propertyVisibleName;
                $p->value = $propertyValue;
                array_push($result, $p);
            }

            $stmt->close();
            return $result;
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_READ_ALL_PROPERTIES->message);
        }
    }
}