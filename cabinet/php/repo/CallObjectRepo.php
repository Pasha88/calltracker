<?php

require_once(dirname(__DIR__) . '/util/Repository.php');
require_once(dirname(__DIR__) . '/util/Util.php');
require_once(dirname(__DIR__) . '/commands/DeleteCallsBeforeDateCommand.php');

class CallObjectRepo extends Repository {

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

    public function purgeOldCalls() {
        $dt = Util::getCurrentServerDate();
        $dt->setTime(0,0);
        $dt->sub(new DateInterval('P7D'));
        $params = array('beforeDate' => Util::formatDate($dt));
        $c = new DeleteCallsBeforeDateCommand($params);
        return  $this->executeTransaction($c);
    }
}