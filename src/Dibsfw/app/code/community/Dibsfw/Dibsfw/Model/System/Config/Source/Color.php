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

class Dibsfw_Dibsfw_Model_System_Config_Source_Color
{
   public function toOptionArray()
    {
        return array(
            array('value'=>'blank', 'label'=>Mage::helper('adminhtml')->__('_none_')),
            array('value'=>'sand',  'label'=>Mage::helper('adminhtml')->__('sand')),
            array('value'=>'grey',  'label'=>Mage::helper('adminhtml')->__('grey')),
            array('value'=>'blue',  'label'=>Mage::helper('adminhtml')->__('blue')),
        );
    }
	
 
}
