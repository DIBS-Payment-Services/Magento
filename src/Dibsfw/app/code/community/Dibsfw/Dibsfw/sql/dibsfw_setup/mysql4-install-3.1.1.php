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
 * @package    Dibsfw_Dibsfw
 * @author     Dibs A/S
 * @copyright  Copyright (c) 2010 Dibs A/S. (http://www.dibs.dk/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment Model
 **/

$installer = $this;

$installer->startSetup();

$tableName_CoreResource = Mage::getSingleton('core/resource')->getTableName('core_resource');
$tableName_SalesFlatOrderPayment = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
$sTablePrefix = Mage::getConfig()->getTablePrefix();
$installer->run("
	delete from ".$tableName_CoreResource." where code = 'dibsfw_setup';
	CREATE TABLE if not exists `".$sTablePrefix."dibs_orderdata` (
  		`orderid` VARCHAR(45) NOT NULL,
  		`transact` VARCHAR(50) NOT NULL,
  		`status` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = unpaid, 1 = paid',
  		`amount` VARCHAR(45) NOT NULL,
  		`currency` VARCHAR(45) NOT NULL,
  		`paytype` VARCHAR(45) NOT NULL,
		`PBB_customerId` VARCHAR(45) NOT NULL,
		`PBB_deliveryAddress` VARCHAR(45) NOT NULL,
		`PBB_deliveryCountryCode` VARCHAR(45) NOT NULL,
		`PBB_deliveryPostalCode` VARCHAR(45) NOT NULL,
		`PBB_deliveryPostalPlace` VARCHAR(45) NOT NULL,
		`PBB_firstName` VARCHAR(45) NOT NULL,
		`PBB_lastName` VARCHAR(45) NOT NULL,
  		`cardnomask` VARCHAR(45) NOT NULL,
  		`cardprefix` VARCHAR(45) NOT NULL,
  		`cardexpdate` VARCHAR(45) NOT NULL,
  		`cardcountry` VARCHAR(45) NOT NULL,
  		`acquirer` VARCHAR(45) NOT NULL,
  		`enrolled` VARCHAR(45) NOT NULL,
  		`fee` VARCHAR(45) NOT NULL,
  		`test` VARCHAR(45) NOT NULL,
  		`uniqueoid` VARCHAR(45) NOT NULL,
		`approvalcode` VARCHAR(45) NOT NULL,
  		`voucher` VARCHAR(45) NOT NULL,
  		`amountoriginal` VARCHAR(45) NOT NULL,
  		`voucheramount` VARCHAR(45) NOT NULL,
  		`voucherpaymentid` VARCHAR(45) NOT NULL,
  		`voucherentry` VARCHAR(45) NOT NULL,
  		`voucherrest` VARCHAR(45) NOT NULL,
		`ordercancellation` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = NotPerformed, 1 = Performed',
		`successaction` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = NotPerformed, 1 = Performed',
		`callback` INTEGER UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = NotPerformed, 1 = Performed'
	);
	UPDATE ".$tableName_SalesFlatOrderPayment." SET method='Dibsfw' WHERE method='dibs_standard';
");

$installer->endSetup();