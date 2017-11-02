<?php

require_once(dirname(__DIR__)."/Command.php");

class SavePropertyCommand extends Command {

    private $savePropertySQL = 'insert into application_property(name, value) values(?,?)';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)
    {
        if ($stmt = $conn->prepare($this->savePropertySQL)) {
            $stmt->bind_param("ss", $this->args['name'], $this->args['value']);
            $stmt->execute();
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_SAVE_PROPERTY->message);
        }
        return true;
    }
}