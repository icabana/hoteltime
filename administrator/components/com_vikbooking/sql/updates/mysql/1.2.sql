ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `val_pcent` tinyint(1) NOT NULL DEFAULT 2;
ALTER TABLE `#__vikbooking_seasons` ADD COLUMN `losoverride` varchar(512) DEFAULT NULL;
CREATE TABLE IF NOT EXISTS `#__vikbooking_restrictions` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`name` varchar(128) NOT NULL DEFAULT 'restriction',`month` tinyint(2) NOT NULL DEFAULT 7,`wday` tinyint(1) DEFAULT NULL,`minlos` tinyint(2) NOT NULL DEFAULT 1,`multiplyminlos` tinyint(1) NOT NULL DEFAULT 0,`maxlos` tinyint(2) NOT NULL DEFAULT 0,PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
INSERT INTO `#__vikbooking_gpayments` (`name`,`file`,`published`,`note`,`charge`,`setconfirmed`,`shownotealw`,`val_pcent`,`ch_disc`) VALUES ('Offline Credit Card','offline_credit_card.php','0','<p></p>','0.00','0','0','1','1');
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `confirmnumber` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `idorderota` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `channel` varchar(64) DEFAULT NULL;
ALTER TABLE `#__vikbooking_orders` ADD COLUMN `chcurrency` varchar(32) DEFAULT NULL;
ALTER TABLE `#__vikbooking_adultsdiff` ADD COLUMN `pernight` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE `#__vikbooking_optionals` ADD COLUMN `ordering` int(10) NOT NULL DEFAULT 1;