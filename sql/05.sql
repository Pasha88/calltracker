ALTER TABLE customer
ADD COLUMN `ya_id` VARCHAR(128) NULL AFTER `time_zone`,
ADD COLUMN `ya_id_auth` VARCHAR(128) NULL AFTER `ya_id`;

ALTER TABLE customer
ADD COLUMN `ya_refresh` VARCHAR(128) NULL AFTER `ya_id_auth`,
ADD COLUMN `ya_expires` BIGINT(20) NULL AFTER `ya_refresh`;

ALTER TABLE call_object
ADD COLUMN `ya_client_id` VARCHAR(256) NULL AFTER `modify_date`;

UPDATE call_type SET `name`='Initial' WHERE `call_type_id`='1';

ALTER TABLE call_object
ADD COLUMN `ya_upload` INT NULL AFTER `ya_client_id`;

CREATE TABLE call_event (
  `call_event_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `event_code` VARCHAR(45) NULL,
  `event_date_time` DATETIME NULL,
  `customer_id` BIGINT(20) NULL,
  `ya_upload` INT NULL,
  PRIMARY KEY (`call_event_id`),
  INDEX `FK_EVENT_CUSTOMER_idx` (`customer_id` ASC),
  CONSTRAINT `FK_EVENT_CUSTOMER`
    FOREIGN KEY (`customer_id`)
    REFERENCES customer (`customer_id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE);

ALTER TABLE call_event
DROP FOREIGN KEY `FK_EVENT_CUSTOMER`;
ALTER TABLE call_event
CHANGE COLUMN `customer_id` `customer_uid` VARCHAR(38) NULL DEFAULT NULL ;
ALTER TABLE call_event
ADD CONSTRAINT `FK_EVENT_CUSTOMER`
  FOREIGN KEY (`customer_uid`)
  REFERENCES customer (`customer_uid`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;

ALTER TABLE `host1563047`.`call_event`
ADD COLUMN `ya_client_id` VARCHAR(256) NULL AFTER `ya_upload`;

ALTER TABLE customer
ADD COLUMN `role` INT NULL AFTER `ya_expires`;

ALTER TABLE customer
ADD COLUMN `up_time_from` INT NULL AFTER `role`,
ADD COLUMN `up_time_to` INT NULL AFTER `up_time_from`;

ALTER TABLE customer
ADD COLUMN `up_time_schedule` INT NULL AFTER `up_time_to`;

ALTER TABLE call_object
CHANGE COLUMN `number_id` `number_id` BIGINT(20) NULL ;

insert into application_property(name, value, visible_name) values('CALLS_PAGE_IDLE_TO_ALIVE_DELAY', '5', 'Задержка по истечении периода неактивности (перед выдачей номера, сек)');

ALTER TABLE call_object
ADD COLUMN `url` VARCHAR(2048) NULL AFTER `ya_upload`;

update customer set time_zone = null where time_zone = 0;

insert into call_type(call_type_id, name) values(-1, 'NoFreeNumber');