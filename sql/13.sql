CREATE TABLE deploy_scripts (
  `num` INT NOT NULL,
  PRIMARY KEY (`num`));

ALTER TABLE deploy_scripts
ADD COLUMN `exec_date` DATETIME NULL AFTER `num`;


ALTER TABLE deploy_scripts
ADD COLUMN `file_name` VARCHAR(256) NULL AFTER `exec_date`;

ALTER TABLE deploy_scripts
CHANGE COLUMN `exec_date` `exec_date` DATETIME NOT NULL ;

insert into deploy_scripts values(12, now(), '12.sql');
insert into deploy_scripts values(13, now(), '13.sql');