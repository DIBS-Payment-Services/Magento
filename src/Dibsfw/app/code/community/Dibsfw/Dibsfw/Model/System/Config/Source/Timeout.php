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

class Dibsfw_Dibsfw_Model_System_Config_Source_Timeout
{
    public function toOptionArray() {
        $options =  array();
        foreach ($this->getTimes() as $time => $label) {
            $options[] = array(
               'value' => $time,
               'label' => $label
            );
        } 

        return $options;
    }

    private function getTimes() {
        $hDibs = Mage::helper('dibsfw'); 
        return array(
            -1 => $hDibs->__('Never'),
            30 => $hDibs->__('%s hour', '0,5'),
            60 => $hDibs->__('%s hour', '1'),
            90 => $hDibs->__('%s hour', '1,5'),
            120 => $hDibs->__('%s hour', '2'),
            240 => $hDibs->__('%s hour', '4'),
            480 => $hDibs->__('%s hour', '8'),
            960 => $hDibs->__('%s hour', '16'),
            1440 => $hDibs->__('%s day', '1'),
            2880 => $hDibs->__('%s days', '2'),
        );
    }
}