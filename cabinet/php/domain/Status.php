<?php

class Status {
    public const PENDING = 1;
    public const WAITING_FOR_CAPTURE = 2;
    public const SUCCEEDED = 3;
    public const CANCELED = 4;
    public const CAPTURE_FAILED = -1;
    public const WAITING_FOR_CAPTURE_WRONG_NOTIFICATION = -2;

    public static function byCode($code) {
        switch (strtolower($code)) {
            case "pending":
                return self::PENDING;
            case "waiting_for_capture":
                return self::WAITING_FOR_CAPTURE;
            case "succeeded":
                return self::SUCCEEDED;
            case "canceled":
                return self::CANCELED;
            case "capture_failed":
                return self::CAPTURE_FAILED;
            case "waiting_for_capture_wrong_notification":
                return self::WAITING_FOR_CAPTURE_WRONG_NOTIFICATION;
            default:
                throw new Exception("Указан неверный статус платежа");
        }
    }
}
