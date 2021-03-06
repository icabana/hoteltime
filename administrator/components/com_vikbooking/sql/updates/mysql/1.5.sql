ALTER TABLE `#__vikbooking_orders` CHANGE `channel` `channel` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
UPDATE `#__vikbooking_orders` SET `channel`=NULL;
CREATE TABLE IF NOT EXISTS `#__vikbooking_countries` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`country_name` char(64) DEFAULT NULL,`country_3_code` char(3) DEFAULT NULL,`country_2_code` char(2) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `lang` varchar(10) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `country` varchar(5) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `tot_taxes` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `tot_city_taxes` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `tot_fees` decimal(12,2) DEFAULT NULL;
ALTER TABLE `#__vikbooking_seasons` CHANGE `diffcost` `diffcost` DECIMAL( 12, 3 ) NULL DEFAULT NULL;
ALTER TABLE `#__vikbooking_optionals` ADD COLUMN `is_citytax` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_optionals` ADD COLUMN `is_fee` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `breakfast_included` tinyint(1) DEFAULT 0;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `free_cancellation` tinyint(1) DEFAULT 0;
ALTER TABLE `#__vikbooking_prices` ADD COLUMN `canc_deadline` int(5) DEFAULT 0;
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('taxsummary','0');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('smartsearch','automatic');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('maxdate','+2y');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('firstwday','0');
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `year` int(5) DEFAULT NULL;
ALTER TABLE `#__vikbooking_rooms` ADD COLUMN `imgcaptions` text DEFAULT NULL;
ALTER TABLE `#__vikbooking_ordersrooms` ADD COLUMN `childrenage` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikbooking_ordersrooms` ADD COLUMN `t_first_name` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikbooking_ordersrooms` ADD COLUMN `t_last_name` varchar(64) DEFAULT NULL;