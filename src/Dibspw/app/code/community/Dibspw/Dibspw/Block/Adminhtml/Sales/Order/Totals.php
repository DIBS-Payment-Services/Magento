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

class Dibspw_Dibspw_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
       parent::_initTotals();
    
       // If Order processed with fee, we add it to Totals Order view 
       $feeAmount = ($this->getOrder()->getFeeAmount())/100;
       
       if($feeAmount) {
        $this->_totals['grand_total'] = new Varien_Object(array(
            'code'      => 'grand_total',
            'strong'    => true,
            'value'     => $this->getSource()->getGrandTotal()     +  $feeAmount,
            'base_value'=> $this->getSource()->getBaseGrandTotal() +  $feeAmount,
            'label'     => $this->helper('sales')->__('Grand Total'),
            'area'      => 'footer'
        ));
        
        $this->_totals['due'] = new Varien_Object(array(
            'code'      => 'due',
            'strong'    => true,
            'value'     => ($this->getSource()->getBaseTotalDue()> 0) ? $this->getSource()->getBaseTotalDue() + $feeAmount : $this->getSource()->getBaseTotalDue(),
            'base_value'=> ($this->getSource()->getBaseTotalDue()> 0) ? $this->getSource()->getBaseTotalDue() + $feeAmount : $this->getSource()->getBaseTotalDue(),
            'label'     => $this->helper('sales')->__('Total Due'),
            'area'      => 'footer'
        ));
                
     
        $this->addTotalBefore(new Varien_Object(array(
                'code'      => 'fee',
                'value'     => $feeAmount,
                'base_value'=> $feeAmount,
                'label'     => "Fee",
            ), array('shipping', 'tax')));
       }
        return $this;
    }

}
?>