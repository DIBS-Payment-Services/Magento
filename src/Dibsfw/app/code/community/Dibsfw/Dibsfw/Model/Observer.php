<?php
class Dibsfw_Dibsfw_Model_Observer extends Mage_Core_Model_Abstract
{
     public function order_cancel_after(Varien_Event_Observer $observer) {
        $order = $observer->getOrder();
        if( $order->getPayment()->getMethod() != 'Dibsfw' ) {
            return $this;
        }
        $transactionid = Mage::helper('dibsfw')->getTransactionId($order->getRealOrderId());
        
        if($transactionid) {
            $dibspw = Mage::getModel('dibsfw/dibsfw');
            $dibspw->cancel($order->getPayment());
        }
        return $this;
    } 
}    
?>