<?php
class Dibspw_Dibspw_Block_Adminhtml_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Totals
{
	
  protected $_creditmemo = null;

    public function getCreditmemo()
    {
        if ($this->_creditmemo === null) {
            if ($this->hasData('creditmemo')) {
                $this->_creditmemo = $this->_getData('creditmemo');
            } elseif (Mage::registry('current_creditmemo')) {
                $this->_creditmemo = Mage::registry('current_creditmemo');
            } elseif ($this->getParentBlock()->getCreditmemo()) {
                $this->_creditmemo = $this->getParentBlock()->getCreditmemo();
            }
        }
        return $this->_creditmemo;
    }


    /**
     * Get totals source object
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getCreditmemo();
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        
        $feeAmount = $this->getOrder()->getFeeAmount()/100;
        if($feeAmount && $this->getSource()->getGrandTotal() > 0) {
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
            'base_value'=> $this->getSource()->getBaseGrandTotal() + $feeAmount,
            'label'     => $this->helper('sales')->__('Grand Total'),
            'area'      => 'footer'
        ));
        }
        return $this;
    } 

}
