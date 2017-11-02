<?php

require_once(dirname(__DIR__)."/util/ErrorRegistry.php");

class Command {

    public $errorRegistry;

    public function __construct()
    {
        $this->errorRegistry = new ErrorRegistry();
    }

    public function resultOK() {
        $r = new stdClass();
        $r->result = true;
        return $r;
    }

    public function resultErr($msg) {
        $r = new stdClass();
        $r->result = false;
        $r->message = $msg;
        return $r;
    }


    public function getErrorRegistry() {
        return $this->errorRegistry;
    }

}