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
);