<?php
class Dibspw_Dibspw_Model_Observer extends Mage_Core_Model_Abstract
{
    
     public function order_cancel_after(Varien_Event_Observer $observer) {
        $order = $observer->getOrder();
        $transactionid = Mage::helper('dibspw')->getTransactionId($order->getRealOrderId());
        if($transactionid) {
            $dibspw = Mage::getModel('dibspw/dibspw');
            $dibspw->cancel($order->getPayment());
        }
        return $this;
    } 
}    
?>
