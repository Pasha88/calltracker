<?php

class Status {
    public const PENDING = 1;
    public const WAITING_FOR_CAPTURE = 2;
    public const SUCCEEDED = 3;
    public const CANCELED = 4;
    public const CAPTURE_FAILED = -1;
    public const WAITING_FOR_CAPTURE_WRONG_NOTIFICATION = -2;
}
