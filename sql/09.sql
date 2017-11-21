INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('-1', 'CAPTURE_FAILED', 'Ошибка подтверждения');

ALTER TABLE balance_operation
CHANGE COLUMN `oper_id` `oper_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;

ALTER TABLE tariff 
ADD COLUMN `is_deleted` INT NOT NULL DEFAULT 0 AFTER `rate`;

ALTER TABLE tariff
CHANGE COLUMN `is_deleted` `is_deleted` INT(1) NOT NULL DEFAULT '0' ;

DROP function IF EXISTS `uuid_from_bin`;

DELIMITER $$
CREATE FUNCTION `uuid_from_bin` (b BINARY(16))
RETURNS CHAR(36) DETERMINISTIC
BEGIN
DECLARE hex CHAR(32);
SET hex = HEX(b);
RETURN LOWER(CONCAT(LEFT(hex, 8), '-', MID(hex, 9,4), '-', MID(hex, 13,4), '-', MID(hex, 17,4), '-', RIGHT(hex, 12)));
END$$

DELIMITER ;

