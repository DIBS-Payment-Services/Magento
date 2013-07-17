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

class Dibsfw_Dibsfw_Model_System_Config_Source_Lang {
   public function toOptionArray() {
        return array(
            //array('value'=>'auto', 'label'=>Mage::helper('adminhtml')->__('Auto')),
            array('value'=>'da',   'label'=>Mage::helper('adminhtml')->__('Danish')),
            array('value'=>'nl',   'label'=>Mage::helper('adminhtml')->__('Dutch')),
            array('value'=>'en',   'label'=>Mage::helper('adminhtml')->__('English')),
            array('value'=>'fo',   'label'=>Mage::helper('adminhtml')->__('Faroese')),
            array('value'=>'fi',   'label'=>Mage::helper('adminhtml')->__('Finnish')),
            array('value'=>'fr',   'label'=>Mage::helper('adminhtml')->__('French')),
            array('value'=>'de',   'label'=>Mage::helper('adminhtml')->__('German')),
            array('value'=>'it',   'label'=>Mage::helper('adminhtml')->__('Italian')),
            array('value'=>'no',   'label'=>Mage::helper('adminhtml')->__('Norwegian')),
            array('value'=>'pl',   'label'=>Mage::helper('adminhtml')->__('Polish')),
            array('value'=>'es',   'label'=>Mage::helper('adminhtml')->__('Spanish')),
            array('value'=>'sv',   'label'=>Mage::helper('adminhtml')->__('Swedish')),
        );
    }
	
 
}
