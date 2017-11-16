ALTER TABLE customer_tariff
RENAME TO  customer_tariff_history ;

insert into customer_tariff_history
  SELECT customer_id, 1, now()
  from customer
;

ALTER TABLE balance_operation
DROP FOREIGN KEY `FK_BAL_OPER_ORDERS`;
ALTER TABLE balance_operation
DROP INDEX `UNIQUE_BAL_OPER_ORDER` ;

ALTER TABLE balance_operation
ADD UNIQUE INDEX `order_id_UNIQUE` (`order_id` ASC);
