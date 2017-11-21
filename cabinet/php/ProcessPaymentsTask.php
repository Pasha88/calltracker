<?php

require_once(dirname(__DIR__) . '/handlers/YkHandler.php');

$handler = new YkHandler();
$handler->processScheduled();