<?php

require_once (dirname(__DIR__) . "/commands/GetAdMetadataCommand.php");

class GAUtil {

    private $repository;
    private $errorRegistry;

    /**
     * SimpleRest constructor.
     */
    public function __construct()
    {
        $this->repository = new Repository();
        $this->errorRegistry = new ErrorRegistry();
    }

    public function sendEventWithMeta($gaId, $cid, $url, $category, $actionId) {
        $gaCurl = curl_init();
        curl_setopt($gaCurl,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $getParams = '?payload_data&v=1' . '&tid=' . $gaId .  '&cid=' . $cid . '&t=event' . '&ec=' . $category . '&ea=' . curl_escape($gaCurl, $actionId) . '&uip=' . Util::get_client_ip_address();

        curl_setopt_array($gaCurl, array(
            CURLOPT_URL => AppConfig::GA_MEASUREMENT_PROTO_URL . $getParams,
            CURLOPT_RETURNTRANSFER => true,
        ));
        $response = curl_exec($gaCurl);

        if($response === false) {
            curl_close($gaCurl);
            throw new Exception($this->errorRegistry->USER_ERR_GA_SEND->message);
        }

        curl_close($gaCurl);
    }

    //    public function sendEvent($numberId, $callObjectId, $url, $category, $actionId) {
//        $meta = null;
//        $params = array('numberId' => $numberId, 'callObjectId' => $callObjectId);
//        $command = new GetGaMetadataCommand($params);
//        try {
//            $meta = $this->repository->executeTransaction($command);
//        }
//        catch(Exception $ex) {
//            throw new Exception($ex->getMessage());
//        }
//
//        if($meta == null || $meta->gaId == null || strlen($meta->gaId) == 0 || $meta->cid == null || strlen($meta->cid) == 0) {
//            throw new Exception( $this->errorRegistry->USER_ERR_NO_GA_ID->message);
//        }
//
//        $this->sendEventWithMeta($meta->gaId, $meta->cid, $url, $category, $actionId);
//    }

}