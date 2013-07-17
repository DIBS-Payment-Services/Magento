<?php
class Dibspw_Dibspw_Block_Adminhtml_Sales_Order_Invoice_Totals extends Mage_Adminhtml_Block_Sales_Totals
{
  
    
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
       
        $feeAmount    = $this->getOrder()->getFeeAmount()/100;
        if ($feeAmount) {
            $this->addTotalBefore(new Varien_Object(array(
                'code'      => 'fee',
                'value'     => $feeAmount,
                'base_value'=> $feeAmount,
                'label'     => "Fee",
            ), array('shipping', 'tax')));
            
           $this->_totals['grand_total'] = new Varien_Object(array(
            'code'      => 'grand_total',
            'strong'    => true,
            'value'     => $this->getSource()->getGrandTotal()     +  $feeAmount,
            'base_value'=> $this->getSource()->getBaseGrandTotal() +  $feeAmount,
            'label'     => $this->helper('sales')->__('Grand Total'),
            'area'      => 'footer'
        ));
        }
        return $this;
    }
}
