ALTER TABLE balance_operation
ADD UNIQUE INDEX `UNIQUE_BAL_OPER_ORDER` (`order_id` ASC);
