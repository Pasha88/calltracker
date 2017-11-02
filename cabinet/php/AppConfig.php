<?php

class AppConfig {

    public const HOST_PROTO_PREFIX = 'http://';
    public const LOGIN_LINK = "http://www.allostat.ru/cabinet/#/login";
    public const APP_HOST = 'www.allostat.ru';
//    public const OCCUPY_NUMBER_URL = "http://develop2.allostat.ru/api/freenumber";
    public const OCCUPY_NUMBER_URL = "http://localhost:8100/api/freenumber";
    public const GA_MEASUREMENT_PROTO_URL = 'https://www.google-analytics.com/collect';

    public const YA_APPLICATION_ID = '8cf808c53605423cbe114d2916d94134';
    public const YA_APPLICATION_KEY = 'ff4fcfdebf48432bab6667b28caf015e';
    public const YA_COUNTER_URL = "https://api-metrika.yandex.ru/management/v1/counter/";
    public const YA_LOAD_UPDATE_TOKEN_URL = "https://oauth.yandex.ru/token";
    public const YA_TOKEN_NOT_VALID = 'TOKEN_NOT_VALID';
    public const YA_TOKEN_NOT_VALID_OK = 'TOKEN_NOT_VALID_OK';

    public const CALL_TYPE_GA_ERROR = 1;
    public const CALL_TYPE_HAS_CALL = 2;
    public const CALL_TYPE_NO_CALL = 3;
    public const GA_MEASUREMENT_EVENT_CATEGORY_ALLOSTAT = 'Allostat';
    public const GA_MEASUREMENT_EVENT_ACTION_HAS_CALL = 'Входящий звонок';
    public const GA_MEASUREMENT_EVENT_ACTION_NUMBER_ISSUED = 'Выдан номер';
    public const GA_MEASUREMENT_EVENT_ACTION_NO_FREE_NUMBER = 'Нет свободного номера';

    public const DB_NAME = "host1563047";
    public const DB_SERVER = "localhost";
    public const DB_USER = "api_user";
    public const DB_PWD = "lsGer6ham";



}

