create table customer (
	`customer_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `hkey` varchar(2000) NOT NULL,
    `description` varchar(2000),
    PRIMARY KEY (`customer_id`)
);

create table call_type (
	`call_type_id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(128) NOT NULL,
    `description` varchar(2000),
    PRIMARY KEY (`call_type_id`)
);

CREATE TABLE `number_pool` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `free_date_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY(`customer_id`) REFERENCES customer(`customer_id`)
);

create table call_object (
	`call_object_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `client_id` varchar(72) NOT NULL,
    `call_date_time` datetime NOT NULL,
    `type_id` int NOT NULL,
    `description` varchar(2000),
    PRIMARY KEY (`call_object_id`),
    FOREIGN KEY (`type_id`) REFERENCES call_type(`call_type_id`)
);alter table call_object add customer_id bigint(20);
alter table call_object add foreign key(customer_id) references customer(customer_id);

ALTER TABLE customer ADD UNIQUE INDEX `email_UNIQUE` (`email` ASC);

ALTER TABLE call_object ADD COLUMN `number_id` BIGINT(20) NOT NULL AFTER `customer_id`;

insert into call_type(call_type_id, name) values(1, 'Registered');
insert into call_type(call_type_id, name) values(2, 'Call');
insert into call_type(call_type_id, name) values(3, 'NoCall');

ALTER TABLE call_object ADD COLUMN `modify_date` DATETIME NULL AFTER `number_id`;

ALTER TABLE customer
ADD COLUMN `restore_uid` VARCHAR(45) NULL AFTER `description`,
ADD COLUMN `restore_valid_till` DATETIME NULL AFTER `restore_uid`;

ALTER TABLE customer
ADD COLUMN `reset_pwd_uid` VARCHAR(45) NULL AFTER `restore_valid_till`,
ADD COLUMN `reset_pwd_valid_till` DATETIME NULL AFTER `reset_pwd_uid`;

ALTER TABLE customer
ADD COLUMN `ga_id` VARCHAR(128) NULL AFTER `reset_pwd_valid_till`;

ALTER TABLE customer
ADD COLUMN `default_phone_number` VARCHAR(20) NULL AFTER `ga_id`;

update call_type set name = 'GA error' where call_type_id = 1CREATE TABLE support_request (
  `request_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `customer_id` BIGINT(20) NOT NULL,
  `request_content` VARCHAR(8192) NOT NULL,
  `request_status_id` INT NOT NULL,
  `create_date_time` DATETIME NOT NULL,
  `close_date_time` DATETIME NULL,
  `last_status_date` DATETIME NULL,
  PRIMARY KEY (`request_id`));

  CREATE TABLE support_request_file (
  `request_file_id` BIGINT(20) NOT NULL,
  `name` VARCHAR(2048) NULL,
  `file_content` MEDIUMBLOB NULL,
  `size` INT NULL,
  PRIMARY KEY (`request_file_id`));
  
  ALTER TABLE support_request_file
ADD COLUMN `request_id` BIGINT(20) NULL AFTER `size`,
ADD INDEX `FK_REQUEST_ID_idx` (`request_id` ASC);
ALTER TABLE support_request_file
ADD CONSTRAINT `FK_REQUEST_ID`
  FOREIGN KEY (`request_id`)
  REFERENCES support_request (`request_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;
  
  ALTER TABLE support_request
ADD INDEX `FK_SUPP_REQ_CUST_idx` (`customer_id` ASC);
ALTER TABLE support_request
ADD CONSTRAINT `FK_SUPP_REQ_CUST`
  FOREIGN KEY (`customer_id`)
  REFERENCES customer (`customer_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

  CREATE TABLE supp_req_status (
  `req_status_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `description` VARCHAR(1000) NULL,
  PRIMARY KEY (`req_status_id`));
  
  ALTER TABLE support_request
ADD INDEX `FK_SUPP_REQ_STATUS_idx` (`request_status_id` ASC);
ALTER TABLE support_request
ADD CONSTRAINT `FK_SUPP_REQ_STATUS`
  FOREIGN KEY (`request_status_id`)
  REFERENCES supp_req_status (`req_status_id`)
  ON DELETE NO ACTION
  ON UPDATE CASCADE;
  
ALTER TABLE support_request_file
CHANGE COLUMN `request_file_id` `file_id` BIGINT(20) NOT NULL ,
CHANGE COLUMN `name` `name` VARCHAR(2048) NOT NULL ,
CHANGE COLUMN `size` `size` INT(11) NOT NULL , RENAME TO  file_object;
  
  ALTER TABLE file_object
CHANGE COLUMN `name` `filename` VARCHAR(2048) NOT NULL ;

ALTER TABLE file_object
CHANGE COLUMN `size` `filesize` INT(11) NOT NULL ;

ALTER TABLE file_object
ADD COLUMN `tmp_filename` VARCHAR(2048) NULL AFTER `request_id`;

ALTER TABLE file_object
CHANGE COLUMN `file_id` `file_id` BIGINT(20) NOT NULL AUTO_INCREMENT ;

ALTER TABLE file_object
ADD COLUMN `file_type` VARCHAR(45) NULL AFTER `tmp_filename`;

insert into supp_req_status(req_status_id, name, description) values(1, 'Registered', 'Заявка принята');

ALTER TABLE customer 
ADD COLUMN `default_domain` VARCHAR(256) NULL AFTER `default_phone_number`;

-- allostat2 version
ALTER TABLE customer
ADD COLUMN `script_token` VARCHAR(4096) NULL AFTER `default_domain`;

ALTER TABLE customer 
ADD COLUMN `customer_uid` VARCHAR(38) NULL AFTER `script_token`,
ADD UNIQUE INDEX `customer_uid_UNIQUE` (`customer_uid` ASC);

ALTER TABLE customer
ADD COLUMN `time_zone` INT NOT NULL DEFAULT 0 AFTER `customer_uid`;

ALTER TABLE customer
CHANGE COLUMN `time_zone` `time_zone` INT(11) NULL ;

CREATE TABLE application_property (
  `id` INT NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `value` VARCHAR(2048) NULL,
  PRIMARY KEY (`id`));

ALTER TABLE application_property
ADD UNIQUE INDEX `name_UNIQUE` (`name` ASC);

ALTER TABLE application_property
CHANGE COLUMN `name` `name` VARCHAR(128) NULL ;

ALTER TABLE application_property
CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT ;

ALTER TABLE application_property
ADD COLUMN `visible_name` VARCHAR(128) NULL AFTER `value`,
ADD COLUMN `description` VARCHAR(1024) NULL AFTER `visible_name`;

insert into application_property(name, value, visible_name) values('PHONE_NUMBER_BUSY_INTERVAL_SEC','300', 'Период действия подстановки номера (сек)');
insert into application_property(name, value, visible_name) values('RESTORE_TOKEN_EXPIRE_INTERVAL_SEC','600', 'Срок действия ссылки на восстановление пароля (сек)');
insert into application_property(name, value, visible_name) values('RESTORE_PWD_TOKEN_EXPIRE_INTERVAL_SEC','180', 'Срок действия страницы восстановления пароля (сек)');
insert into application_property(name, value, visible_name) values('MAIL_SMTP_HOST','mail.allostat.ru', ' Почта - DNS-имя сервера');
insert into application_property(name, value, visible_name) values('MAIL_SMTP_PORT','25', 'Почта - Порт для отправки');
insert into application_property(name, value, visible_name) values('MAIL_SMTP_USER','support@allostat.ru', 'Почта - учетная запись для отправки');
insert into application_property(name, value, visible_name) values('MAIL_SMTP_PWD','2890217', 'Почта - пароль учетной записи для отправки');
insert into application_property(name, value, visible_name) values('SERVER_TIMEZONE','Europe/Moscow', 'Временная зона на сервере');
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

insert into call_type(call_type_id, name) values(-1, 'NoFreeNumber');-- Биллинг ------
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
