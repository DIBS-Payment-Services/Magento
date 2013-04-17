<?php

/**
 * Dibs A/S
 * Dibs Payment Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Payments & Gateways Extensions
 * @package    Dibspw_Dibspw
 * @author     Dibs A/S
 * @copyright  Copyright (c) 2010 Dibs A/S. (http://www.dibs.dk/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment Model
 **/

$installer = $this;

$installer->startSetup();
$sTableName_CoreResource = Mage::getSingleton('core/resource')->getTableName('core_resource');
$sTableName_SalesFlatOrderPayment = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
$sTablePrefix = Mage::getConfig()->getTablePrefix();
$flatOrderTable = $this->getTable('sales/order');
$installer->run("
	delete from " . $sTableName_CoreResource . " where code = 'dibspw_setup';
	CREATE TABLE IF NOT EXISTS `" . $sTablePrefix . "dibspw_results` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `orderid` varchar(100) NOT NULL DEFAULT '',
                `status` varchar(10) NOT NULL DEFAULT '0',
                `testmode` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `transaction` varchar(100) NOT NULL DEFAULT '',
                `amount` int(10) unsigned NOT NULL DEFAULT '0',
                `currency` smallint(3) unsigned NOT NULL DEFAULT '0',
                `fee` int(10) unsigned NOT NULL DEFAULT '0',
                `paytype` varchar(32) NOT NULL DEFAULT '',
                `voucheramount` int(10) unsigned NOT NULL DEFAULT '0',
                `amountoriginal` int(10) unsigned NOT NULL DEFAULT '0',
                `ext_info` text,
                `validationerrors` text,
                `capturestatus` varchar(10) NOT NULL DEFAULT '0',
                `actioncode` varchar(20) NOT NULL DEFAULT '',
                `success_action` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = NotPerformed, 1 = Performed',
                `cancel_action` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = NotPerformed, 1 = Performed',
                `callback_action` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = NotPerformed, 1 = Performed',
                `success_error` varchar(100) NOT NULL DEFAULT '',
                `callback_error` varchar(100) NOT NULL DEFAULT '',
                `sysmod` varchar(10) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`),
                KEY `orderid` (`orderid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
	UPDATE " . $sTableName_SalesFlatOrderPayment . " SET method='Dibspw' WHERE method='dibspw_standard';
        
");

 if( !$installer->getConnection()->tableColumnExists($flatOrderTable, 'fee_amount') ) {
   $installer->getConnection()->addColumn($flatOrderTable, 'fee_amount', 'int(11)' ); 
 } 
$installer->endSetup(); 