<?php

require_once(dirname(__DIR__)."/Command.php");

class SaveAllPropertiesCommand extends Command {

//    private $readAllPropertiesSQL = 'select count(1) from application_property where name = ?';
//    private $savePropertySQL = 'insert into application_property(name, value) values(?,?)';
    private $updatePropertySQL = 'update application_property set value = ? where name = ?';
    private $args;

    function __construct($a) {
        $this->args = $a;
        parent::__construct();
    }

    public function execute($conn)    {
        if ($stmt = $conn->prepare($this->updatePropertySQL)) {
            $propertyName = null;
            $propertyValue = null;

            foreach($this->args['propertyArray'] as $property) {
                $stmt->bind_param("ss", $property->value, $property->name);
                $stmt->execute();
            }
            $stmt->close();
        } else {
            throw new Exception($this->getErrorRegistry()->USER_ERR_UPDATE_ALL_PROPERTIES->message);
        }
        return true;
    }
}