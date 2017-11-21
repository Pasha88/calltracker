LOCK TABLES
customer WRITE,
customer_tariff_history WRITE,
tariff WRITE;

ALTER TABLE customer
  DROP FOREIGN KEY FK_CUSTOMER_TARIFF,
MODIFY tariff_id INT;

ALTER TABLE customer_tariff_history
  DROP FOREIGN KEY FK_CUSTOMER_TARIFF_TARIFF,
MODIFY tariff_id INT;

ALTER TABLE `tariff` MODIFY tariff_id INTEGER NOT NULL AUTO_INCREMENT;

ALTER TABLE `customer`
ADD CONSTRAINT FK_CUSTOMER_TARIFF FOREIGN KEY (tariff_id)
REFERENCES tariff (tariff_id);

ALTER TABLE `customer_tariff_history`
ADD CONSTRAINT FK_CUSTOMER_TARIFF_TARIFF FOREIGN KEY (tariff_id)
REFERENCES tariff (tariff_id);

UNLOCK TABLES;

insert into deploy_scripts values(12, now(), '12.sql');