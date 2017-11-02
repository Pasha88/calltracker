<?php

require_once(dirname(__DIR__)."/Command.php");

class ReadPropertyCommand extends Command {

    private $readPropertySQL = 'select value from application_property where name = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->readPropertySQL )) {
            $propertyValue = "";
            $stmt->bind_param("s", $this->args['name']);
            $stmt->bind_result($propertyValue);
            $stmt->execute();
            $stmt->fetch();
            $stmt->close();

            $result = new stdClass();
            $result->name = $this->args['name'];
            $result->value = $propertyValue;
            return $result;
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_READ_PROPERTY->message);
        }
    }
}