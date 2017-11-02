alter table call_object add customer_id bigint(20);
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

update call_type set name = 'GA error' where call_type_id = 1