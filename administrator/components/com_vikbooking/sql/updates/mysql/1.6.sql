ALTER TABLE `#__vikbooking_orders` ADD COLUMN `phone` varchar(32) DEFAULT NULL;
ALTER TABLE `#__vikbooking_iva` CHANGE `aliq` `aliq` decimal(12,3) NOT NULL;
ALTER TABLE `#__vikbooking_iva` ADD COLUMN `breakdown` varchar(512) DEFAULT NULL;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `idprices` varchar(256) DEFAULT NULL;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `promo` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `promotxt` text DEFAULT NULL;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `promodaysadv` int(5) DEFAULT NULL;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `promominlos` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_rooms` ADD COLUMN `alias` varchar(128) NOT NULL;
ALTER TABLE `#__vikbooking_rooms`CHANGE `params` `params` text NULL;
ALTER TABLE `#__vikbooking_rooms`CHANGE `imgcaptions` `imgcaptions` varchar(1024) NULL;
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('todaybookings','1');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('multilang','1');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('bootstrap','0');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('enablepin','1');
CREATE TABLE IF NOT EXISTS `#__vikbooking_translations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(64) NOT NULL,
  `lang` varchar(16) NOT NULL,
  `reference_id` int(10) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
ALTER TABLE `#__vikbooking_gpayments` ADD COLUMN `ordering` int(5) NOT NULL DEFAULT 1;
ALTER TABLE `#__vikbooking_custfields` ADD COLUMN `isnominative` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_custfields` ADD COLUMN `isphone` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_tmplock` ADD COLUMN `idorder` int(10) DEFAULT NULL;
ALTER TABLE `#__vikbooking_coupons` ADD COLUMN `idcustomer` int(10) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `#__vikbooking_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  `country` varchar(32) DEFAULT NULL,
  `cfields` text DEFAULT NULL,
  `pin` int(5) NOT NULL DEFAULT 0,
  `ujid` int(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
CREATE TABLE IF NOT EXISTS `#__vikbooking_customers_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idcustomer` int(10) NOT NULL,
  `idorder` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;