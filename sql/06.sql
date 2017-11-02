-- Биллинг ------
CREATE TABLE tariff (
  `tariff_id` VARCHAR(38) NOT NULL,
  `tariff_name` VARCHAR(128) NOT NULL,
  `max_phone_number` INT NOT NULL,
  `rate` DECIMAL(15,2) NOT NULL,
  PRIMARY KEY (`tariff_id`));

CREATE TABLE order (
  `order_id` VARCHAR(38) NOT NULL,
  `customer_uid` VARCHAR(38) NOT NULL,
  `order_date` DATETIME NOT NULL,
  `sum` DECIMAL(15,2) NOT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `fk_order_customer_idx` (`customer_uid` ASC),
  CONSTRAINT `fk_order_customer`
    FOREIGN KEY (`customer_uid`)
    REFERENCES customer (`customer_uid`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE);

CREATE TABLE cash_operation (
  `oper_id` BIGINT(20) NOT NULL,
  `customer_uid` VARCHAR(38) NOT NULL,
  `oper_date` DATETIME NOT NULL,
  `sum` DECIMAL(15,2) NOT NULL,
  `dsc` VARCHAR(512) NULL,
  `order_id` VARCHAR(38) NULL,
  PRIMARY KEY (`oper_id`),
  INDEX `FK_CASH_OPER_CUSTOMER_idx` (`customer_uid` ASC),
  INDEX `FK_CASH_OPER_ORDER_idx` (`order_id` ASC),
  CONSTRAINT `FK_CASH_OPER_CUSTOMER`
  FOREIGN KEY (`customer_uid`)
  REFERENCES customer (`customer_uid`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `FK_CASH_OPER_ORDER`
  FOREIGN KEY (`order_id`)
  REFERENCES order (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE);

ALTER TABLE customer
  ADD COLUMN `tariff_id` VARCHAR(38) NULL AFTER `up_time_schedule`,
  ADD INDEX `FK_CUSTOMER_TARIFF_idx` (`tariff_id` ASC);
ALTER TABLE customer
  ADD CONSTRAINT `FK_CUSTOMER_TARIFF`
FOREIGN KEY (`tariff_id`)
REFERENCES tariff (`tariff_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

ALTER TABLE customer
  ADD COLUMN `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.0 AFTER `tariff_id`;

ALTER TABLE cash_operation
  RENAME TO  balance_operation;
  
INSERT INTO tariff VALUES(1, 'Тариф по умолчанию', 1, 0.0);
UPDATE customer SET tariff_id = 1 WHERE tariff_id IS NULL;  
