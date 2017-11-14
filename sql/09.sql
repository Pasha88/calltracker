INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('-1', 'CAPTURE_FAILED', 'Ошибка подтверждения');

ALTER TABLE balance_operation
CHANGE COLUMN `oper_id` `oper_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;

ALTER TABLE tariff 
ADD COLUMN `is_deleted` INT NOT NULL DEFAULT 0 AFTER `rate`;

ALTER TABLE tariff
CHANGE COLUMN `is_deleted` `is_deleted` INT(1) NOT NULL DEFAULT '0' ;
