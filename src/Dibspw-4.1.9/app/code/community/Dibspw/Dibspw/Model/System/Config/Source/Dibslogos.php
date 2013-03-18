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
class Dibspw_Dibspw_Model_System_Config_Source_Dibslogos {

    public function toOptionArray() {
        return array(
            array('value' => 'DIBS',
                  'label' => Mage::helper('adminhtml')->__('DIBS Standard Logo')),
            array('value' => 'PBB',
                  'label' => Mage::helper('adminhtml')->__('DIBS PayByBill Logo')),
            array('value' => 'VISA_SECURE',
                  'label' => Mage::helper('adminhtml')->__('Verified by VISA')),
            array('value' => 'MC_SECURE',
                  'label' => Mage::helper('adminhtml')->__('MasterCard SecureCode')),
            array('value' => 'JCB_SECURE', 
                  'label' => Mage::helper('adminhtml')->__('JCB J/Secure')),
            array('value' => 'PCI',
                  'label' => Mage::helper('adminhtml')->__('PCI')),
            array('value' => 'MC', 
                  'label' => Mage::helper('adminhtml')->__('MasterCard')),
            array('value' => 'MTRO', 
                  'label' => Mage::helper('adminhtml')->__('Maestro')),
            array('value' => 'VISA', 
                  'label' => Mage::helper('adminhtml')->__('Visa')),
            array('value' => 'ELEC',
                  'label' => Mage::helper('adminhtml')->__('Visa Electron')),
            array('value' => 'DIN', 
                  'label' => Mage::helper('adminhtml')->__('Diners Club')),
            array('value' => 'AMEX',
                  'label' => Mage::helper('adminhtml')->__('American Express')),
            array('value' => 'DK', 
                  'label' => Mage::helper('adminhtml')->__('Dankort')),
            array('value' => 'EDK', 
                  'label' => Mage::helper('adminhtml')->__('eDankort')),
            array('value' => 'SEB',
                  'label' => Mage::helper('adminhtml')->__('SEB Direktbetalning')),
            array('value' => 'SHB', 
                  'label' => Mage::helper('adminhtml')->__('SHB Direktbetalning')),
            array('value' => 'FSB', 
                  'label' => Mage::helper('adminhtml')->__('Swedbank Direktbetalning')),
            array('value' => 'SOLO', 
                  'label' => Mage::helper('adminhtml')->__('Nordea Solo-E betalning')),
            array('value' => 'DNB', 
                  'label' => Mage::helper('adminhtml')->__('Danske Netbetaling (Danske Bank)')),
            array('value' => 'MOCA',
                  'label' => Mage::helper('adminhtml')->__('Mobilcash')),
            array('value' => 'BAX', 
                  'label' => Mage::helper('adminhtml')->__('BankAxess')),
            array('value' => 'FFK', 
                  'label' => Mage::helper('adminhtml')->__('Forbrugsforeningen Card')),
            array('value' => 'JCB',
                  'label' => Mage::helper('adminhtml')->__('JCB (Japan Credit Bureau)')),
            array('value' => 'AKTIA', 
                  'label' => Mage::helper('adminhtml')->__('Aktia Web Payment')),
            array('value' => 'ELV', 
                  'label' => Mage::helper('adminhtml')->__('Bank Einzug (eOLV)')),
            array('value' => 'EW', 
                  'label' => Mage::helper('adminhtml')->__('eWire')),
            array('value' => 'GIT', 
                  'label' => Mage::helper('adminhtml')->__('Getitcard')),
            array('value' => 'VAL', 
                  'label' => Mage::helper('adminhtml')->__('Valus')),
            array('value' => 'ING', 
                  'label' => Mage::helper('adminhtml')->__('ING iDeal Payment'))
        );
    }

}