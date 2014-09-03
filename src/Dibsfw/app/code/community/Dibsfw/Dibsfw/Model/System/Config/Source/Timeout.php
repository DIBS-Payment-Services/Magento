<?php
/**
 * DIBS Payment module
 *
 * LICENSE AND USAGE INFORMATION
 * It is NOT allowed to modify, copy or re-sell this file or any
 * part of it. Please contact us by email at support@trollweb.no or
 * visit us at www.trollweb.no if you have any questions about this.
 * Trollweb is not responsible for any problems caused by this file.
 *
 * Visit us at http://www.trollweb.no today!
 *
 * @category   Trollweb
 * @package    Trollweb_Dibs
 * @copyright  Copyright (c) 2013 Trollweb (http://www.trollweb.no)
 * @license    Single-site License
 *
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