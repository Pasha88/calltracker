<?php 

require_once(dirname(__DIR__).'/util/Repository.php');
require_once(dirname(__DIR__).'/util/Util.php');

class SimpleRest {
	
	private $httpVersion = "HTTP/1.1";
	private $repository;

    /**
     * SimpleRest constructor.
     */
    public function __construct()
    {
        $this->repository = new Repository();
    }


    public function handle($command) {
        try{
            $rawData = $this->repository->executeTransaction($command);
            $statusCode = 200;
            if(empty($rawData)) {
                $rawData = array('error' => 'Command ' . get_class($command) . ' result data empty');
            }
        }
        catch(Exception $ex) {
            $statusCode = 500;
            $rawData = new stdClass();
            $rawData->error = $ex->getMessage();
            error_log(Util::normErrMsg("[ " . Util::getCurrentDateFormatted() . " ] ===> (" . $rawData->error . ")"), 0);
        }

        $this->echoResponse($statusCode, $rawData);
    }

    public function handleWithResult($command) {
        $rawData = $this->repository->executeTransaction($command);
        $statusCode = 200;
        if(empty($rawData)) {
            $rawData = array('error' => 'Command ' . get_class($command) . ' result data empty');
        }
        return $rawData;
    }

    public function handleResult($rawData) {
        try{
            $statusCode = 200;
            if(empty($rawData)) {
                $rawData = array('error' => 'Result data empty');
            }
        }
        catch(Exception $ex) {
            $statusCode = 500;
            $rawData = new stdClass();
            $rawData->error = $ex->getMessage();
            error_log(Util::normErrMsg("[ " . Util::getCurrentDateFormatted() . " ] ===> (" . $ex->getMessage() . ")"), 0);
        }

        $this->echoResponse($statusCode, $rawData);
    }

    public function handleError($errorMessage) {
        $statusCode = 500;
        $rawData = new stdClass();
        $rawData->error = $errorMessage;
        error_log(Util::normErrMsg("[ " . Util::getCurrentDateFormatted() . " ] ===> (" . $errorMessage . ")"), 0);
        $this->echoResponse($statusCode, $rawData);
    }

	public function setHttpHeaders($contentType, $statusCode){
		
		$statusMessage = $this -> getHttpStatusMessage($statusCode);
		
		header($this->httpVersion. " ". $statusCode ." ". $statusMessage);		
		header("Content-Type:". $contentType);
		//header('Content-Type: text/html; charset=utf-8');
	}

	public function  postKeys($url,$peremen,$headers) {
        $post_arr=array();
        foreach ($peremen as $key=>$value) {
            $post_arr[]=$key."=".$value;
        }
        $data=implode('&',$post_arr);

        $handle=curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        $response=curl_exec($handle);
        $code=curl_getinfo($handle, CURLINFO_HTTP_CODE);
        return array("code"=>$code,"response"=>$response);
    }
	
	public function getHttpStatusMessage($statusCode){
		$httpStatus = array(
			100 => 'Continue',  
			101 => 'Switching Protocols',  
			200 => 'OK',
			201 => 'Created',  
			202 => 'Accepted',  
			203 => 'Non-Authoritative Information',  
			204 => 'No Content',  
			205 => 'Reset Content',  
			206 => 'Partial Content',  
			300 => 'Multiple Choices',  
			301 => 'Moved Permanently',  
			302 => 'Found',  
			303 => 'See Other',  
			304 => 'Not Modified',  
			305 => 'Use Proxy',  
			306 => '(Unused)',  
			307 => 'Temporary Redirect',  
			400 => 'Bad Request',  
			401 => 'Unauthorized',  
			402 => 'Payment Required',  
			403 => 'Forbidden',  
			404 => 'Not Found',  
			405 => 'Method Not Allowed',  
			406 => 'Not Acceptable',  
			407 => 'Proxy Authentication Required',  
			408 => 'Request Timeout',  
			409 => 'Conflict',  
			410 => 'Gone',  
			411 => 'Length Required',  
			412 => 'Precondition Failed',  
			413 => 'Request Entity Too Large',  
			414 => 'Request-URI Too Long',  
			415 => 'Unsupported Media Type',  
			416 => 'Requested Range Not Satisfiable',  
			417 => 'Expectation Failed',  
			500 => 'Internal Server Error',  
			501 => 'Not Implemented',  
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',  
			504 => 'Gateway Timeout',  
			505 => 'HTTP Version Not Supported');
		return ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $httpStatus[500];
	}

    public function echoResponse($statusCode, $rawData) {
        $requestContentType = $_SERVER['HTTP_ACCEPT'];
        $requestContentType .= "; charset=utf-8";
        $this->setHttpHeaders($requestContentType, $statusCode);

        if(strpos($requestContentType,'application/json') !== false){
            $response = $this->encodeJson($rawData);
            echo Util::convertUnicodeCodepoint($response);
        } else {
            $response = $rawData->error;
            echo $response;
        }
    }

    public function encodeJson($responseData) {
        $jsonResponse = json_encode($responseData, JSON_UNESCAPED_UNICODE );
        return $jsonResponse;
    }

    public function normJsonStr($str){
        $str = preg_replace_callback('/\\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
        return iconv('cp1251', 'utf-8', $str);
    }
}
?>