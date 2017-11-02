CREATE TABLE support_request (
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

