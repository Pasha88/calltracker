<?php

require_once (dirname(__DIR__) . "/commands/GetAdMetadataCommand.php");
require_once (dirname(__DIR__) . "/util/Repository.php");
require_once (dirname(__DIR__) . "/util/Util.php");
require_once (dirname(__DIR__) . "/repo/CustomerRepo.php");
require_once (dirname(__DIR__) . "/AppConfig.php");

class YaUtil {

    private $repository;
    private $errorRegistry;

    public function __construct()
    {
        $this->repository = new Repository();
        $this->errorRegistry = new ErrorRegistry();
    }

    public function sendCallCsv($csvString, $customerId) {
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomer($customerId);

        $filename = basename($customer->customerUid . "_" . Util::getCurrentDateSafeFormatted() . ".csv");

        $url = AppConfig::YA_COUNTER_URL . $customer->yaId . "/offline_conversions/upload_calls?client_id_type=CLIENT_ID&new_goal_name=hasCall" . "&oauth_token=" . $customer->yaIdAuth;

        $params  = "--ABC1234\r\n"
            . "Content-Type: text/csv\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"" . $filename . "\"\r\n"
            . "\r\n"
//            . file_get_contents($file) . "\r\n"
            . $csvString . "\r\n"
            . "--ABC1234--";

        $first_newline      = strpos($params, "\r\n");
        $multipart_boundary = substr($params, 2, $first_newline - 2);
        $request_headers    = array();
        $request_headers[]  = 'Content-Length: ' . strlen($params);
        $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
            . $multipart_boundary;

        $yaCurl = curl_init();
        curl_setopt($yaCurl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($yaCurl, CURLOPT_POST, 1);
        curl_setopt($yaCurl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($yaCurl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($yaCurl, CURLOPT_HTTPHEADER, $request_headers);

        curl_setopt_array($yaCurl, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $request_headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ));

        $response = curl_exec($yaCurl);

        $result = json_decode($response);

        if(isset($result->errors)) {
            foreach($result->errors as $err) {
                if($err->error_type == 'invalid_token') {
                    $customer->yaIdAuth = AppConfig::YA_TOKEN_NOT_VALID;
                    $repo->saveCustomer($customer);
                }
            }
        }

        if(!isset($result->uploading)) {
            curl_close($yaCurl);
            throw new Exception($this->errorRegistry->USER_ERR_YA_UPLOAD_CALLS->message . " [" . $result->message . "] " . '{ ' . $response . ' }');
        }
        curl_close($yaCurl);
        return true;
    }

    public function sendNoFreeNumberCsv($csvString, $customerId)    {
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomer($customerId);
        $filename = basename($customer->customerUid . "_noFreeNum_" . Util::getCurrentDateSafeFormatted() . ".csv");
        $url = AppConfig::YA_COUNTER_URL . $customer->yaId . "/offline_conversions/upload_calls?client_id_type=CLIENT_ID&new_goal_name=NoFreeNumber" . "&oauth_token=" . $customer->yaIdAuth;

        $params  = "--ABC1234\r\n"
            . "Content-Type: text/csv\r\n"
            . "Content-Disposition: form-data; name=\"file\"; filename=\"" . $filename . "\"\r\n"
            . "\r\n"
            . $csvString . "\r\n"
            . "--ABC1234--";

        $first_newline      = strpos($params, "\r\n");
        $multipart_boundary = substr($params, 2, $first_newline - 2);
        $request_headers    = array();
        $request_headers[]  = 'Content-Length: ' . strlen($params);
        $request_headers[]  = 'Content-Type: multipart/form-data; boundary='
            . $multipart_boundary;

        $yaCurl = curl_init();
        curl_setopt($yaCurl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($yaCurl, CURLOPT_POST, 1);
        curl_setopt($yaCurl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($yaCurl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($yaCurl, CURLOPT_HTTPHEADER, $request_headers);

        curl_setopt_array($yaCurl, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $request_headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params
        ));

        $response = curl_exec($yaCurl);

        $result = json_decode($response);
        if(!isset($result->uploading)) {
            curl_close($yaCurl);
            throw new Exception($this->errorRegistry->USER_ERR_YA_UPLOAD_CALLS->message . " [" . $result->message . "] ");
        }

        curl_close($yaCurl);
        return true;
    }

    public function updateYaToken($customerId)    {
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomer($customerId);

        $url = AppConfig::YA_LOAD_UPDATE_TOKEN_URL;

        $yaCurl = curl_init();
        curl_setopt_array($yaCurl, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_URL => $url,
//            CURLOPT_HTTPHEADER => $request_headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(
                array(
                    'grant_type'        => 'refresh_token',
                    'refresh_token'     => $customer->yaRefresh,
                    'client_id'         => AppConfig::YA_APPLICATION_ID,
                    'client_secret'     => AppConfig::YA_APPLICATION_KEY
                ))
        ));

        $response = curl_exec($yaCurl);
        $result = json_decode($response);

        if(isset($result->error)) {
            throw new Exception($result->error_description);
        }

        $customer->yaIdAuth = $result->access_token;
        $customer->yaRefresh = $result->refresh_token;
        $customer->yaExpires = $result->expires_in;
        $repo->saveCustomer($customer);

        curl_close($yaCurl);
        return true;
    }

    public function getCallLoads($customerId) {
        $repo = CustomerRepo::getInstance();
        $customer = $repo->getCustomer($customerId);

//        $url = AppConfig::YA_LOAD_CALLS_URL . $customer->yaId . "/offline_conversions/calls_uploadings?&oauth_token=" . $customer->yaIdAuth;
        $url = AppConfig::YA_COUNTER_URL . $customer->yaId . "/offline_conversions/calls_uploadings";

        $yaCurl = curl_init();
        curl_setopt_array($yaCurl, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array(
                "Authorization: OAuth " . $customer->yaIdAuth,
                "Content-Type: application/x-yametrika+json"
            ),
            CURLOPT_URL => $url
        ));
        $response = curl_exec($yaCurl);

        $result = json_decode($response);
        if(!isset($result->uploadings)) {
            curl_close($yaCurl);
            throw new Exception($this->errorRegistry->USER_ERR_YA_UPLOAD_CALLS->message . " [" . $result->message . "] ");
        }

        curl_close($yaCurl);

        return $response;
    }

    public function checkYaCounterId($yaId, $yaIdAuth) {
//        $repo = CustomerRepo::getInstance();
//        $customer = $repo->getCustomer($customerId);

//        $url = AppConfig::YA_LOAD_CALLS_URL . $customer->yaId . "/offline_conversions/calls_uploadings?&oauth_token=" . $customer->yaIdAuth;
        $url = AppConfig::YA_COUNTER_URL . $yaId;

        $yaCurl = curl_init();
        curl_setopt_array($yaCurl, array(
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => array(
                "Authorization: OAuth " . $yaIdAuth,
                "Content-Type: application/x-yametrika+json"
            ),
            CURLOPT_URL => $url
        ));
        $response = curl_exec($yaCurl);

        $result = json_decode($response);
        if(!isset($result->counter) && isset($result->errors) && $result->code == 403 && $result->errors[0]->error_type != 'invalid_token') {
            curl_close($yaCurl);
            return false;
        }
        if(isset($result->counter)) {
            curl_close($yaCurl);
            return true;
        }
        curl_close($yaCurl);
        return false;
    }
}
