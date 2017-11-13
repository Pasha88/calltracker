<?php

class Status {
    public static const PENDING = 1;
    public static const WAITING_FOR_CAPTURE = 2;
    public static const SUCCEEDED = 3;
    public static const CANCELED = 4;
    public static const CAPTURE_FAILED = -1;
    public static const WAITING_FOR_CAPTURE_WRONG_NOTIFICATION = -2;
}