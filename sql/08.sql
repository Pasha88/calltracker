ALTER TABLE order_status_history
DROP FOREIGN KEY `FK_ORD_STA_CHA_ORDER`;
ALTER TABLE order_status_history
CHANGE COLUMN `order_id` `order_id` BINARY(16) NOT NULL ;

ALTER TABLE balance_operation
DROP FOREIGN KEY `FK_BALANCE_OPER_ORDER`;
ALTER TABLE balance_operation
CHANGE COLUMN `oper_id` `oper_id` BINARY(16) NOT NULL ,
DROP INDEX `FK_BALANCE_OPER_ORDER_idx` ;

ALTER TABLE orders
CHANGE COLUMN `order_id` `order_id` BINARY(16) NOT NULL ;

ALTER TABLE balance_operation
CHANGE COLUMN `oper_id` `oper_id` BIGINT(20) NOT NULL ,
CHANGE COLUMN `order_id` `order_id` BINARY(16) NULL DEFAULT NULL ,
ADD INDEX `FK_BAL_OPER_ORDERS_idx` (`order_id` ASC);
ALTER TABLE balance_operation
ADD CONSTRAINT `FK_BAL_OPER_ORDERS`
  FOREIGN KEY (`order_id`)
  REFERENCES orders (`order_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

ALTER TABLE order_status_history
ADD CONSTRAINT `FK_ORD_STA_ORDERS`
  FOREIGN KEY (`order_id`)
  REFERENCES orders (`order_id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('2', 'WAITING_FOR_CAPTURE', 'Ожидание подтверждения');
INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('3', 'SUCCEEDED', 'Выполнен');
INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('4', 'CANCELED', 'Отменен');
  
ALTER TABLE orders
ADD COLUMN `confirmation_url` VARCHAR(2000) NULL AFTER `status`;

ALTER TABLE customer_tariff
ADD COLUMN `set_date` DATETIME NOT NULL AFTER `tariff_id`,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`customer_id`, `tariff_id`, `set_date`);

ALTER TABLE orders
ADD COLUMN `idempotence_key` BINARY(16) NULL AFTER `confirmation_url`;

ALTER TABLE orders
ADD COLUMN `currency_code` VARCHAR(3) NULL AFTER `sum`;

ALTER TABLE orders
ADD COLUMN `create_date` DATETIME NULL AFTER `customer_uid`;

ALTER TABLE orders
DROP FOREIGN KEY `FK_ORDER_TARIFF`;
ALTER TABLE orders
DROP COLUMN `tariff_id`,
DROP INDEX `FK_ORDER_TARIFF_idx`;
