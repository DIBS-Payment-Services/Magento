<?php
$installer = $this;
$sTablePrefix = Mage::getConfig()->getTablePrefix();
$tableName =  $sTablePrefix . "dibs_pw_results"; 
if ($installer->getConnection()->isTableExists($tableName)) {
    $this->startSetup()
    ->run("ALTER TABLE `".$tableName."` ADD `acquirerDeliveryAddress` VARCHAR( 250 ) NOT NULL ,
           ADD `acquirerDeliveryCountryCode` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerDeliveryPostalCode` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerDeliveryPostalPlace` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerFirstName` VARCHAR( 50 ) NOT NULL ,
           ADD `acquirerLastName` VARCHAR( 50 ) NOT NULL;")->endSetup();
} 