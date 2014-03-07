<?php

$sTablePrefix = Mage::getConfig()->getTablePrefix();

$this->startSetup()
    ->run("ALTER TABLE `".$sTablePrefix."dibs_pw_results` ADD `acquirerDeliveryAddress` VARCHAR( 250 ) NOT NULL ,
           ADD `acquirerDeliveryCountryCode` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerDeliveryPostalCode` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerDeliveryPostalPlace` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerFirstName` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerLastName` VARCHAR( 50 ) NOT NULL;")->endSetup(); 