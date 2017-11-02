<?php
/* 
A domain Class to demonstrate RESTful web services
*/

//require_once(dirname(__DIR__)."/domain/PhoneNumber.php");
//require_once("Util.php");

//Class PhoneNumberPool {
//
//	private static $_instance = null;
//
//	private function __construct() {
//	}
//	protected function __clone() {}
//
//	static public function getInstance() {
//		if(is_null(self::$_instance)) {
//			self::$_instance = new self();
//			self::$_instance->numberPool = array(
//				0 => new PhoneNumber(0, "+74951111111", ""),
//				1 => new PhoneNumber(1, "+74952222222", ""),
//				2 => new PhoneNumber(2, "+74953333333", ""),
//				3 => new PhoneNumber(3, "+74954444444", ""),
//				4 => new PhoneNumber(4, "+74955555555", ""),
//			);
//		}
//		echo is_null(self::$_instance);
//		return self::$_instance;
//	}
//
//	public function getFreeNumber() {
//		$dateNow = new DateTime("now", Util::getMoscowTz());
//
//		$result = array();
//		for($i = 0; $i<count($this->numberPool); $i++) {
//			$dfts = $this->numberPool[$i]->freeDateTime->getTimestamp() - $dateNow->getTimestamp();
//
//			if($dfts <= 0) {
//				$this->numberPool[$i]->freeDateTime = new DateTime("now", Util::getMoscowTz());
//				$di = new DateInterval(Util::$busyInterval);
//				$this->numberPool[$i]->freeDateTime->add($di);
//				$result[$dfts] = $this->numberPool[$i]->value;
//				return $result;
//			}
//			$result[$dfts] = "No Number";
//		}
//		return $result;
//	}
//
//}
