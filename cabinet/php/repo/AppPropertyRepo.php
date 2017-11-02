<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/commands/applicationProperty/SavePropertyCommand.php');
require_once(dirname(__DIR__) . '/commands/applicationProperty/ReadPropertyCommand.php');
require_once(dirname(__DIR__) . '/commands/applicationProperty/ReadAllPropertiesCommand.php');
require_once(dirname(__DIR__) . '/commands/applicationProperty/SaveAllPropertiesCommand.php');

class AppPropertyRepo extends Repository {

    private static $_instance = null;

    private function __construct() {}
    protected function __clone() {}

    static public function getInstance() {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function read($name) {
        $params = array('name' => $name);
        $c = new ReadPropertyCommand($params);
        $result = $this->executeTransaction($c);
        return $result->value;
    }

    public function save($name, $value) {
        $params = array('name' => $name, 'value' => $value);
        $c = new SavePropertyCommand($params);
        $this->executeTransaction($c);
    }

    public function readAll() {
        $c = new ReadAllPropertiesCommand();
        return $this->executeTransaction($c);
    }

    public function saveAll($propertyArray) {
        $params = array('propertyArray' => $propertyArray);
        $c = new SaveAllPropertiesCommand($params);
        return $this->executeTransaction($c);
    }

}
