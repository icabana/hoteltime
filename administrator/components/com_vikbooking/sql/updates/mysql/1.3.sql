INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('numrooms','5');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('numadults','1-10');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('numchildren','0-4');
INSERT INTO `#__vikbooking_config` (`param`,`setting`) VALUES ('autodefcalnights','1');
ALTER TABLE `#__vikbooking_rooms` ADD COLUMN `totpeople` int(10) NOT NULL DEFAULT 1;
UPDATE `#__vikbooking_rooms` SET `totpeople`=`toadult` + `tochild`;
ALTER TABLE `#__vikbooking_optionals` ADD COLUMN `ageintervals` varchar(256) DEFAULT NULL;