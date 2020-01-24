INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('minautoremove','0');
ALTER TABLE `#__vikbooking_ordersrooms` ADD COLUMN `otarplan` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers_orders` ADD COLUMN `signature` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers_orders` ADD COLUMN `pax_data` text DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers_orders` ADD COLUMN `comments` text DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers_orders` ADD COLUMN `checkindoc` varchar(128) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `checked` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_gpayments` ADD COLUMN `hidenonrefund` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `canc_policy` text DEFAULT NULL;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `minlos` tinyint(2) NOT NULL DEFAULT 1;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `minhadv` int(5) DEFAULT 0;
ALTER TABLE `#__vikbooking_customers` ADD COLUMN `gender` char(1) DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers` ADD COLUMN `bdate` varchar(16) DEFAULT NULL;
ALTER TABLE `#__vikbooking_customers` ADD COLUMN `pbirth` varchar(64) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `#__vikbooking_orderhistory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idorder` int(10) NOT NULL,
  `dt` datetime NOT NULL,
  `type` char(2) NOT NULL DEFAULT 'C',
  `descr` text DEFAULT NULL,
  `totpaid` decimal(12,2) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `#__vikbooking_receipts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(10) NOT NULL,
  `idorder` int(10) NOT NULL,
  `created_on` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;