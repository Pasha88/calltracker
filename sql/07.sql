CREATE TABLE order_status (
  `order_status_id` INT NOT NULL,
  `code` VARCHAR(45) NULL,
  `dsc` VARCHAR(45) NULL,
  PRIMARY KEY (`order_status_id`));

ALTER TABLE customer
DROP FOREIGN KEY `FK_CUSTOMER_TARIFF`;

update customer set tariff_id = null where tariff_id is not null;
delete from tariff where tariff_id = '1';

ALTER TABLE tariff
CHANGE COLUMN `tariff_id` `tariff_id` INT NOT NULL ;

ALTER TABLE customer
CHANGE COLUMN `tariff_id` `tariff_id` INT NULL DEFAULT NULL ,
ADD INDEX `FK_CUSTOMER_TARIFF_idx` (`tariff_id` ASC);
ALTER TABLE customer
ADD CONSTRAINT `FK_CUSTOMER_TARIFF`
  FOREIGN KEY (`tariff_id`)
  REFERENCES tariff (`tariff_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

INSERT INTO tariff VALUES(1, 'Тариф по умолчанию', 1, 0.0);
UPDATE customer SET tariff_id = 1 WHERE tariff_id IS NULL;

ALTER TABLE order
ADD COLUMN `tariff_id` INT NULL AFTER `sum`,
ADD INDEX `FK_ORDER_TARIFF_idx` (`tariff_id` ASC);
ALTER TABLE order
ADD CONSTRAINT `FK_ORDER_TARIFF`
  FOREIGN KEY (`tariff_id`)
  REFERENCES tariff (`tariff_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

ALTER TABLE order
ADD COLUMN `status` INT NULL AFTER `tariff_id`,
ADD INDEX `FK_ORDER_ORDER_STATUS_idx` (`status` ASC);
ALTER TABLE order
ADD CONSTRAINT `FK_ORDER_ORDER_STATUS`
  FOREIGN KEY (`status`)
  REFERENCES order_status (`order_status_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

CREATE TABLE customer_tariff (
  `customer_id` BIGINT(20) NOT NULL,
  `tariff_id` INT NOT NULL,
  PRIMARY KEY (`customer_id`, `tariff_id`),
  INDEX `FK_CUSTOMER_TARIFF_TARIFF_idx` (`tariff_id` ASC),
  CONSTRAINT `FK_CUSTOMER_TARIFF_CUSTOMER`
    FOREIGN KEY (`customer_id`)
    REFERENCES customer (`customer_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `FK_CUSTOMER_TARIFF_TARIFF`
    FOREIGN KEY (`tariff_id`)
    REFERENCES tariff (`tariff_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE);

CREATE TABLE order_status_history (
  `order_id` VARCHAR(38) NOT NULL,
  `order_status_id` INT NOT NULL,
  `change_status_time` DATETIME NULL,
  PRIMARY KEY (`order_id`, `order_status_id`),
  INDEX `FK_ORD_STA_CHA_ORD_STA_idx` (`order_status_id` ASC),
  CONSTRAINT `FK_ORD_STA_CHA_ORDER`
    FOREIGN KEY (`order_id`)
    REFERENCES order (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `FK_ORD_STA_CHA_ORD_STA`
    FOREIGN KEY (`order_status_id`)
    REFERENCES order_status (`order_status_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE);

INSERT INTO order_status (`order_status_id`, `code`, `dsc`) VALUES ('1', 'PENDING', 'В обработке');

ALTER TABLE order_status_history
DROP FOREIGN KEY `FK_ORD_STA_CHA_ORDER`;

ALTER TABLE balance_operation
DROP FOREIGN KEY `FK_CASH_OPER_ORDER`;
ALTER TABLE balance_operation
DROP INDEX `FK_CASH_OPER_ORDER_idx` ;

ALTER TABLE order
CHANGE COLUMN `order_id` `order_id` BIGINT(20) NOT NULL ;

ALTER TABLE balance_operation
CHANGE COLUMN `order_id` `order_id` BIGINT(20) NULL DEFAULT NULL ,
ADD INDEX `FK_BALANCE_OPER_ORDER_idx` (`order_id` ASC);
ALTER TABLE balance_operation
ADD CONSTRAINT `FK_BALANCE_OPER_ORDER`
  FOREIGN KEY (`order_id`)
  REFERENCES order (`order_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

ALTER TABLE order_status_history
CHANGE COLUMN `order_id` `order_id` BIGINT(20) NOT NULL ;
ALTER TABLE order_status_history
ADD CONSTRAINT `FK_ORD_STA_CHA_ORDER`
  FOREIGN KEY (`order_id`)
  REFERENCES order (`order_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;
