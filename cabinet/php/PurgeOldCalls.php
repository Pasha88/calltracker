<?php

require_once(dirname(__DIR__) . '/php/repo/CallObjectRepo.php');

$r = CallObjectRepo::getInstance();
$r->purgeOldCalls();
print("----------- Старые звонки успешно удалены ------------");
