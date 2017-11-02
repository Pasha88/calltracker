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
