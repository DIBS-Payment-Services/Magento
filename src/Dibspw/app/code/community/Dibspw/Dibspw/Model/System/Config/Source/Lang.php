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
class Dibspw_Dibspw_Model_System_Config_Source_Lang {

    public function toOptionArray() {
        return array(
            array('value' => 'da_DK', 'label' => Mage::helper('adminhtml')->__('Danish')),
            array('value' => 'en_UK', 'label' => Mage::helper('adminhtml')->__('English')),
            array('value' => 'nb_NO', 'label' => Mage::helper('adminhtml')->__('Norwegian')),
            array('value' => 'sv_SE', 'label' => Mage::helper('adminhtml')->__('Swedish')),
        );
    }
}