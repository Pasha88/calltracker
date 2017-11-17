<?php
$yaCurl = curl_init();
$url = "https://allostat.ru/public_api/pnservice";

$param = new stdClass();
$param->type = "notification";
$param->event = "payment.waiting_for_capture";
$param->object = [
    "id" => "21a0f4b4-000f-500a-b000-02b090209c40",
    "status" => "waiting_for_capture",
    "paid" => true,
    "amount" => [
        "value" => "100.00",
        "currency" => "RUB"
    ],
    "created_at" => "2017-11-17T16:15:32.007Z",
    "expires_at" => "2017-12-30T10:39:15.469Z",
    "metadata" => "",
    "payment_method" => [
        "type" => "bank_card",
        "id" => "8f8daf85-86ce-4f7b-94e1-196b4a56f12e",
        "saved" => false,
        "card" => [
            "last4" => "4448",
            "expiry_month" => "04",
            "expiry_year" => "2018",
            "card_type" => "MasterCard"
        ],
        "title" => "Bank card *1062"
    ]
];

$data_string = json_encode($param);

curl_setopt_array($yaCurl, array(
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ),
        CURLOPT_POSTFIELDS => $data_string
    ));



$response = curl_exec($yaCurl);
$i=0;


//        http_build_query(

//        array(
//            "type" => "notification",
//            "event" => "payment.waiting_for_capture",
//            "object" => [
//                "id" => "219e304f-000f-500a-b000-016760c7e472",
//                "status" => "waiting_for_capture",
//                "paid" => true,
//                "amount" => [
//                    "value" => "135.00",
//                    "currency" => "RUB"
//                ],
//                "created_at" => "2017-11-15T12:26:16.007Z",
//                "expires_at" => "2017-10-31T10:39:15.469Z",
//                "metadata" => "",
//                "payment_method" => [
//                    "type" => "bank_card",
//                    "id" => "8f8daf85-86ce-4f7b-94e1-196b4a56f12e",
//                    "saved" => false,
//                    "card" => [
//                        "last4" => "4448",
//                        "expiry_month" => "04",
//                        "expiry_year" => "2018",
//                        "card_type" => "MasterCard"
//                    ],
//                    "title" => "Bank card *1062"
//                ]
//            ]
//        ))
//)