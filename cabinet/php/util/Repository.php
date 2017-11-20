<?php

require_once(dirname(__DIR__).'/AppConfig.php');

class Repository {

	function init(){
		$dbname = AppConfig::DB_NAME ;
		$servername = AppConfig::DB_SERVER;
		$username = AppConfig::DB_USER;
		$password = AppConfig::DB_PWD;

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		
		// Check connection
		if ($conn->connect_error) {
			throw new Exception("Connection failed: " . $conn->connect_error);
		}
        $conn->autocommit(FALSE);
		return $conn;
	}

	function close($connection) {
        $thread = $connection->thread_id;
        $connection->kill($thread);
    }
	
	public function executeQuery($queryString) {
		$conn = $this->init();
		$res = $conn->query($queryString);
        $conn->commit();
        $this->close($conn);
		return $res;
	}

	public function executeTransaction($command) {
        $rawData = null;
        $conn = $this->init();
        try {
            $rawData = $command->execute($conn);

            $lastErr = error_get_last();
            if(isset($lastErr) && $lastErr['type'] == 1) {
                throw new Exception($lastErr['message'] . "\r\n" . $lastErr['file'] . " line[" . $lastErr['line'] . "]");
            }

            $conn->commit();
            $this->close($conn);
        }
        catch(Exception $ex) {
            $conn->rollback();
            $this->close($conn);
            throw new Exception($ex->getMessage());
        }
        return $rawData;
    }

}
