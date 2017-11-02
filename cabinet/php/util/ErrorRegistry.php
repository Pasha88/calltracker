<?php

require_once("ErrCode.php");

class ErrorRegistry
{
    public $E001;
    public $E002;
    public $E003;
    public $E004;
    public $E005;

    public $USER_ERR_EMAIL_EXISTS;
    public $USER_ERR_REGISTRATION;
    public $USER_ERR_CHANGE_PWD;
    public $USER_ERR_GET_PHONE_NUMBER_POOL;
    public $USER_ERR_CUSTOMER_NOT_EXISTS;
    public $USER_ERR_PASSWORD_ERROR;
    public $USER_ERR_SEARCH_CALLS;
    public $USER_ERR_SEARCH_CALLS_COUNT;
    public $USER_ERR_SAVE_CALL;
    public $USER_ERR_GET_FREE_PHONE_NUMBER;
    public $USER_ERR_OCCUPY_PHONE_NUMBER;
    public $USER_ERR_NO_FREE_PHONE_NUMBER;
    public $USER_ERR_CALL_STATE_CHANGE;
    public $USER_ERR_FIND_CUSTOMER_BY_ID;
    public $USER_ERR_SAVE_RESTORE_TOKEN;
    public $USER_ERR_NO_SUCH_USER_OR_RESTORE_EXPIRED;
    public $USER_ERR_FIND_RESTORE;
    public $USER_ERR_FIND_CUSTOMER_BY_EMAIL;
    public $USER_ERR_CLEAR_RESTORE_TOKEN;
    public $USER_ERR_SAVE_RESET_PWD_TOKEN;
    public $USER_ERR_NO_SUCH_USER_OR_RESTORE_PWD_TOKEN_EXPIRED;
    public $USER_ERR_CLEAR_RESTORE_PWD_TOKEN;
    public $USER_ERR_GET_GA_ID;
    public $USER_ERR_NO_GA_ID;
    public $USER_ERR_GA_SEND;
    public $USER_ERR_GA_ID_SAVE;
    public $USER_ERR_DEFAULT_NUMBER_SAVE;
    public $USER_ERR_NO_CID_ID;
    public $USER_ERR_GET_CID;
    public $USER_ERR_DELETE_CALL;
    public $USER_ERR_CHECK_HAS_NEW_CALL_ERROR;
    public $USER_ERR_GET_LAST_CALL_ID;
    public $USER_ERR_PWD_INVALID;
    public $USER_ERR_EMAIL_INVALID;
    public $USER_ERR_SAVE_FILE;
    public $USER_ERR_DELETE_FILE;
    public $USER_ERR_CREATE_SUPPORT_REQUEST;
    public $USER_ERR_BIND_SUPPORT_REQUEST_FILES;
    public $USER_ERR_GET_FILE_CONTENT;
    public $USER_ERR_SAVE_SETTINGS;
    public $USER_ERR_UPDATE_ALL_PROPERTIES;
    public $USER_ERR_READ_ALL_PROPERTIES;
    public $USER_ERR_READ_PROPERTY;
    public $USER_ERR_SAVE_PROPERTY;
    public $USER_ERR_SAVE_CUSTOMER;
    public $USER_ERR_YA_UPLOAD_CALLS;
    public $USER_ERR_GET_YA_CALLS_BY_CUSTOMER;
    public $USER_ERR_UPDATE_CALL_STATE;
    public $USER_ERR_GET_ALL_CUSTOMER_ID;
    public $USER_ERR_NO_FREE_NUMBER_EVENT_SAVE;
    public $USER_ERR_GET_NO_FREE_NUMBER_EVENTS;
    public $USER_ERR_UPDATE_CALL_EVENT_STATE;
    public $USER_ERR_GET_YESTERDAY_YA_LOAD_CALLS;
    public $USER_ERR_GET_ALL_CUSTOMERS;
    public $USER_ERR_YA_REFRESH_TOKEN;
    public $USER_ERR_FIND_NUMBER_POOL;
    public $USER_ERR_NUMBER_POOL_NOT_EXISTS;
    public $USER_ERR_SAVE_NUMBER_POOL;
    public $USER_ERR_DELETE_CALLS_BEFORE_DATE;
    public $USER_ERR_GET_ALL_TARIFFS;
    public $USER_ERR_GET_CUSTOMER_ORDERS;
    public $USER_ERR_UPDATE_CUSTOMER_ORDER;

    public function __construct()
    {
        $this->E001 = new ErrCode('E-001', "Ошибка при выполнении запроса на проверку email");
        $this->E002 = new ErrCode('E-002', "Ошибка сохранения данных клиента");

        $this->USER_ERR_EMAIL_EXISTS = new ErrCode('USER_ERR_EMAIL_EXISTS', "Пользователь с таким Email уже зарегистрирован");
        $this->USER_ERR_REGISTRATION = new ErrCode('USER_ERR_EMAIL_EXISTS', "Ошибка регистрации");
        $this->USER_ERR_CHANGE_PWD = new ErrCode('USER_ERR_CHANGE_PWD', "Ошибка смены пароля");
        $this->USER_ERR_GET_PHONE_NUMBER_POOL = new ErrCode('USER_ERR_GET_PHONE_NUMBER_POOL', "Ошибка получения пула номеров");
        $this->USER_ERR_CUSTOMER_NOT_EXISTS = new ErrCode('USER_ERR_CUSTOMER_NOT_EXISTS', "Клиент с указанными реквизитами не найден");
        $this->USER_ERR_PASSWORD_ERROR = new ErrCode('USER_ERR_PASSWORD_ERROR', "Неверный пароль");
        $this->USER_ERR_SEARCH_CALLS = new ErrCode('USER_ERR_SEARCH_CALLS', "Ошибка при поиске звонков");
        $this->USER_ERR_SEARCH_CALLS_COUNT = new ErrCode('USER_ERR_SEARCH_CALLS_COUNT', "Ошибка при получении общего количества звонков");
        $this->USER_ERR_SAVE_CALL = new ErrCode('USER_ERR_SAVE_CALL', "Ошибка при сохранении данных звонка");
        $this->USER_ERR_GET_FREE_PHONE_NUMBER = new ErrCode('USER_ERR_GET_FREE_PHONE_NUMBER', "Ошибка при получении свободного телефонного номера из пула");
        $this->USER_ERR_OCCUPY_PHONE_NUMBER = new ErrCode('USER_ERR_OCCUPY_PHONE_NUMBER', "Ошибка при резервировании телефонного номера из пула");
        $this->USER_ERR_NO_FREE_PHONE_NUMBER = new ErrCode('USER_ERR_NO_FREE_PHONE_NUMBER', "Все телефонные номера пула заняты или пул номеров пуст");
        $this->USER_ERR_CALL_STATE_CHANGE = new ErrCode('USER_ERR_CALL_STATE_CHANGE', "Ошибка смены статуса звонка");
        $this->USER_ERR_FIND_CUSTOMER_BY_ID = new ErrCode('USER_ERR_FIND_CUSTOMER_BY_ID', "Ошибка получения данных клиента по ID");
        $this->USER_ERR_SAVE_RESTORE_TOKEN = new ErrCode('USER_ERR_SAVE_RESTORE_TOKEN', "Ошибка сохранения токена восстановления пароля");
        $this->USER_ERR_NO_SUCH_USER_OR_RESTORE_EXPIRED = new ErrCode('USER_ERR_NO_SUCH_USER_OR_RESTORE_EXPIRED', "Ссылка на восстановление устарела или пользователя не существует");
        $this->USER_ERR_FIND_RESTORE = new ErrCode('USER_ERR_FIND_RESTORE', "Ошибка поиска токена восстановления");
        $this->USER_ERR_FIND_CUSTOMER_BY_EMAIL = new ErrCode('USER_ERR_FIND_CUSTOMER_BY_EMAIL', "Ошибка поиска пользователя по email");
        $this->USER_ERR_CLEAR_RESTORE_TOKEN = new ErrCode('USER_ERR_CLEAR_RESTORE_TOKEN', "Ошибка очистки токена");
        $this->USER_ERR_SAVE_RESET_PWD_TOKEN = new ErrCode('USER_ERR_SAVE_RESTORE_PWD_TOKEN', "Ошибка сохранения токена сброса пароля");
        $this->USER_ERR_NO_SUCH_USER_OR_RESTORE_PWD_TOKEN_EXPIRED = new ErrCode('USER_ERR_NO_SUCH_USER_OR_RESTORE_PWD_TOKEN_EXPIRED', "Ссылка на изменение пароля устарела или пользователя не существует");
        $this->USER_ERR_CLEAR_RESTORE_PWD_TOKEN = new ErrCode('USER_ERR_CLEAR_RESTORE_PWD_TOKEN', "Ошибка очистки токена изменения пароля");
        $this->USER_ERR_GET_GA_ID = new ErrCode('USER_ERR_GET_GA_ID', "Ошибка при получении GA ID");
        $this->USER_ERR_NO_GA_ID = new ErrCode('USER_ERR_NO_GA_ID', "Не удается получить GA ID");
        $this->USER_ERR_GA_SEND = new ErrCode('USER_ERR_GA_SEND', "Ошибка отправки данных в GA");
        $this->USER_ERR_GA_ID_SAVE = new ErrCode('USER_ERR_GA_ID_SAVE', "Ошибка сохранения GA ID");
        $this->USER_ERR_DEFAULT_NUMBER_SAVE = new ErrCode('USER_ERR_DEFAULT_NUMBER_SAVE', "Ошибка сохранения номера по умолчанию");
        $this->USER_ERR_NO_CID_ID = new ErrCode('USER_ERR_NO_CID_ID', "Отсутствует client id google analytics");
        $this->USER_ERR_GET_CID = new ErrCode('USER_ERR_GET_CID', "Ошибка при получении client id google analytics");
        $this->USER_ERR_DELETE_CALL = new ErrCode('USER_ERR_GET_CID', "Ошибка при удалении звонка из таблицы");
        $this->USER_ERR_CHECK_HAS_NEW_CALL_ERROR = new ErrCode('USER_ERR_CHACK_HAS_NEW_CALL_ERROR', "Ошибка при проверке на наличие новых звонков");
        $this->USER_ERR_GET_LAST_CALL_ID = new ErrCode('USER_ERR_GET_LAST_CALL_ID', "Ошибка при получении последнего ID звонка");
        $this->USER_ERR_PWD_INVALID = new ErrCode('USER_ERR_PWD_INVALID', "Пароль должен быть длиной не менее 3 символов и может состоять только из латинских букв, цифр, символов (!@#$%) и знака подчеркивания");
        $this->USER_ERR_EMAIL_INVALID = new ErrCode('USER_ERR_EMAIL_INVALID', "Укажите корректный email в качестве логина");
        $this->USER_ERR_SAVE_FILE = new ErrCode('USER_ERR_SAVE_FILE', "Не удалось сохранить файл");
        $this->USER_ERR_DELETE_FILE = new ErrCode('USER_ERR_DELETE_FILE', "Не удалось удалить файл");
        $this->USER_ERR_CREATE_SUPPORT_REQUEST = new ErrCode('USER_ERR_CREATE_SUPPORT_REQUEST', "Не удалось сохранить запрос на поддержку");
        $this->USER_ERR_BIND_SUPPORT_REQUEST_FILES = new ErrCode('USER_ERR_BIND_SUPPORT_REQUEST_FILES', "Не удалось приложить файлы к запросу на поддержку");
        $this->USER_ERR_GET_FILE_CONTENT = new ErrCode('USER_ERR_GET_FILE_CONTENT', "Ошибка при получении содержимого файлов");
        $this->USER_ERR_SAVE_SETTINGS = new ErrCode('USER_ERR_SAVE_SETTINGS', "Ошибка при сохранении пользовательских настроек");
        $this->USER_ERR_UPDATE_ALL_PROPERTIES = new ErrCode('USER_ERR_UPDATE_ALL_PROPERTIES', "Ошибка сохранения списка настроек приложения");
        $this->USER_ERR_READ_ALL_PROPERTIES = new ErrCode('USER_ERR_READ_ALL_PROPERTIES', "Ошибка чтения списка настроек приложения");
        $this->USER_ERR_READ_PROPERTY = new ErrCode('USER_ERR_READ_PROPERTY', "Ошибка чтения настройки приложения");
        $this->USER_ERR_SAVE_PROPERTY = new ErrCode('USER_ERR_SAVE_PROPERTY', "Ошибка сохранения настройки приложения");
        $this->USER_ERR_SAVE_CUSTOMER = new ErrCode('USER_ERR_SAVE_PROPERTY', "Ошибка сохранения данных клиента");
        $this->USER_ERR_YA_UPLOAD_CALLS = new ErrCode('USER_ERR_YA_UPLOAD_CALLS', "Ошибка загрузки звонков");
        $this->USER_ERR_GET_YA_CALLS_BY_CUSTOMER = new ErrCode('USER_ERR_GET_YA_CALLS_BY_CUSTOMER', "Ошибка получения данных о звонках для клиента");
        $this->USER_ERR_UPDATE_CALL_STATE = new ErrCode('USER_ERR_UPDATE_CALL_STATE', "Ошибка обновления статуса звонка по id");
        $this->USER_ERR_GET_ALL_CUSTOMER_ID = new ErrCode('USER_ERR_GET_ALL_CUSTOMER_ID', "Ошибка обновления списка ID всех клиентов");
        $this->USER_ERR_NO_FREE_NUMBER_EVENT_SAVE = new ErrCode('USER_ERR_NO_FREE_NUMBER_EVENT_SAVE', "Ошибка сохранения события \"Нет свободного номера\"");
        $this->USER_ERR_GET_NO_FREE_NUMBER_EVENTS = new ErrCode('USER_ERR_GET_NO_FREE_NUMBER_EVENTS', "Ошибка получения списка событий \"Нет свободного номера\"");
        $this->USER_ERR_UPDATE_CALL_EVENT_STATE = new ErrCode('USER_ERR_UPDATE_CALL_EVENT_STATE', "Ошибка обновления статуса события \"Нет свободного номера\"");
        $this->USER_ERR_GET_YESTERDAY_YA_LOAD_CALLS = new ErrCode('USER_ERR_GET_YESTERDAY_YA_LOAD_CALLS', "Ошибка получения статуса загрузки статистики яндекс по вчерашним звонкам");
        $this->USER_ERR_GET_ALL_CUSTOMERS = new ErrCode('USER_ERR_GET_ALL_CUSTOMERS', "Ошибка получения списка объектов клиентов (Customer)");
        $this->USER_ERR_YA_REFRESH_TOKEN = new ErrCode('USER_ERR_GET_ALL_CUSTOMERS', "Ошибка обновления токена яндекс метрики");
        $this->USER_ERR_FIND_NUMBER_POOL = new ErrCode('USER_ERR_FIND_NUMBER_POOL', "Ошибка поиска телефонного номера");
        $this->USER_ERR_NUMBER_POOL_NOT_EXISTS = new ErrCode('USER_ERR_NUMBER_POOL_NOT_EXISTS', "Указанного телефонного номера не существует");
        $this->USER_ERR_SAVE_NUMBER_POOL = new ErrCode('USER_ERR_SAVE_NUMBER_POOL', "Ошибка сохранения телефонного номера");
        $this->USER_ERR_DELETE_CALLS_BEFORE_DATE = new ErrCode('USER_ERR_DELETE_CALLS_BEFORE_DATE', "Ошибка удаления звонков (до даты)");
        $this->USER_ERR_GET_ALL_TARIFFS = new ErrCode('USER_ERR_GET_ALL_TARIFFS', "Ошибка получения списка тарифов");
        $this->USER_ERR_GET_CUSTOMER_ORDERS = new ErrCode('USER_ERR_GET_CUSTOMER_ORDERS', "Ошибка получения списка платежей клиента");
        $this->USER_ERR_UPDATE_CUSTOMER_ORDER = new ErrCode('USER_ERR_UPDATE_CUSTOMER_ORDER', "Ошибка обновления платежа клиента");
        $this->USER_ERR_INSERT_CUSTOMER_ORDER = new ErrCode('USER_ERR_INSERT_CUSTOMER_ORDER', "Ошибка создания платежа клиента");
    }

}