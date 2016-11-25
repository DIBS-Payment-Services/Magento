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

require_once  Mage::getBaseDir('code').'/community/Dibspw/Dibspw/Model/dibs_api/pw/dibs_pw_api.php';

class Dibspw_Dibspw_Model_Dibspw extends dibs_pw_api {
	     
    protected $_canReviewPayment        = true;
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;
    protected $_isInitializeNeeded      = true;

    /**
     * Payment method code
     *
     * @var string
     */
 
    
      public function authorize(Varien_Object $payment, $amount) {
        return $this;
    }
  


     
    /* 
     * Validate the currency code is avaliable to use for dibs or not
     */
    public function validate() {
        parent::validate();
        return $this;
    }
    
    public function getCheckoutFormFields() {
        $oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $aFields = $this->api_dibs_get_requestFields($oOrder);
        
        return $aFields;
    }
    
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('Dibspw/Dibspw/redirect', array('_secure' => true));
    }
	
	/** For capture **/
    public function capture(Varien_Object $payment, $amount)
    {
        $errorMsg         = null;  
        $incrementOrderId = $payment->getOrder()->getIncrementId();
        $transactionId    = $this->getDIBSTransactionId($incrementOrderId);
        $result           = $this->callDibsApi($payment, $amount, 'CaptureTransaction');
        
        switch ($result['status']) {
            case 'ACCEPT':
                   $this->setTransactionCaptureStatus($payment, $transactionId);
                break;

            case 'PENDING':
                $this->setTransactionCaptureStatus($payment, $transactionId);
                Mage::getSingleton('core/session')->addNotice(Mage::helper('dibspw')->__('DIBSPW_LABEL_37'));
                break;

            case 'DECLINE':
                    // Transaction was placed with capturenow = 1 parameter, 
                    // DIBS return DECLINED but we capture this transaction
                     if($this->isTransactionAuthoraizedWithCapture($incrementOrderId)) {
                        $this->setTransactionCaptureStatus($payment, $transactionId);
                        Mage::getSingleton('core/session')->addNotice(Mage::helper('dibspw')->__('DIBSPW_LABEL_38'));
                     } else { // This capture API call was really DECLINED
                         $errorMsg = $this->_getHelper()->__("DIBS returned DECLINE check your payment in DIBS admin. Error msg: ".$result['message']);
                         $this->log("'Capture' DIBS API call returned DECLINE. Error message:".$result['message'], $transactionId);
                     }
                break;
            case 'ERROR':
                     $errorMsg = $this->_getHelper()->__("DIBS returned ERROR check your payment in DIBS admin. Error msg: ".$result['message']);
                     $this->log("'Refund' DIBS API call returned ERROR. Error message:".$result['message'], $transactionId);
                break;
     }
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
         $errorMsg          = null;
         $incrementOrderId  = $payment->getOrder()->getIncrementId();
         $transactionId     = $this->getDIBSTransactionId($incrementOrderId);
         $result            = $this->callDibsApi($payment,$amount,'RefundTransaction');
         
         switch ($result['status']) {
            case 'ACCEPT':
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
            break;
        
            case 'PENDING':
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
                Mage::getSingleton('core/session')->addNotice(Mage::helper('dibspw')->__('DIBSPW_LABEL_39'));
            break;

            case 'DECLINE':
                $errorMsg = $this->_getHelper()->__("Refund attempt was DECLINED" . $result['message']);
                $this->log("'Refund' DIBS API call returned DECLINE. Error message:".$result['message'], $transactionId);
            break;

            case 'ERROR':
                 $errorMsg = $this->_getHelper()->__("Error due online refund" . $result['message']);
                 $this->log("'Refund' DIBS API call returned ERROR. Error message:".$result['message'], $transactionId);
            break;

         }
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
       return $this;
    }
    
    public function cancelOrdersAfterTimeout() {
        $definedTimeout = $this->helper_dibs_tools_conf('timeout');
        if ($definedTimeout < 0) {
            return $this;
        }
        $timeout = date('Y-m-d H:i:s', time()-($definedTimeout * 60));
        $orders = Mage::getModel('sales/order')->getCollection()->
            join(array('op' => 'sales/order_payment'), 'op.entity_id = main_table.entity_id', 'method')->
            addAttributeToFilter('method', array('eq' => 'Dibspw'))
            ->addFieldToFilter('updated_at', array('lt' => $timeout))
            ->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT))
            ->setPageSize(25);

        foreach ($orders as $order) {
            $order->cancel()->save();
        }
        return $this;
    }
    
    public function cancel(Varien_Object $payment) {
       $incrementOrderId  = $payment->getOrder()->getIncrementId();
       $transactionId     = $this->getDIBSTransactionId($incrementOrderId);
       $result            = $this->callDibsApi($payment,0,'CancelTransaction');
      
       switch ($result['status']) {
           case 'ACCEPT':
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_VOID);
           break;
     
           case 'ERROR':
               $this->log("'Cancel' DIBS API call returned ERROR. Error message:".$result['message'], $transactionId);
               Mage::getSingleton('core/session')
                       ->addNotice($this->_getHelper()
                       ->__("'Cancel' DIBS API call returned ERROR. Use DIBS Admin panel to manually cancel this transaction" . $result['message']));
           break;      
           
           case 'DECLINE':
                $this->log("'Cancel' DIBS API call was DECLINED. Error message:".$result['message'], $transactionId);
                Mage::getSingleton('core/session')
                        ->addNotice($this->_getHelper()
                        ->__("Cancel DIBS API call was DECLINED. Use DIBS Admin panel to manually cancel this transaction" . $result['message']));
           break;
        }
        return $this;
    }
    
    
    private function log($msg, $transaction) {
         $message = array('message' => $msg, 'transaction' => $transaction);
         Mage::log($message, null, 'dibs_pw.log');
    }
    
    
    private function getDIBSTransactionId( $incermentOrderId ) {
         $read = Mage::getSingleton("core/resource")->getConnection("core_read");
         $selectStmt = $read->select()->from(array('t' => Mage::getConfig()->getTablePrefix()."dibs_pw_results"), 
                                             array('transaction'))->where("orderid=?", $incermentOrderId)->query();
         $result = $selectStmt->fetchAll();  
         return $result[0]['transaction'];
    }
    
    private function isTransactionAuthoraizedWithCapture( $incermentOrderId ) {
         $captured = false;
        
         $read = Mage::getSingleton("core/resource")->getConnection("core_read");
         $selectStmt = $read->select()->from(array('t' => Mage::getConfig()->getTablePrefix()."dibs_pw_results"), 
                                             array('capturestatus'))->where("orderid=?", $incermentOrderId)->query();
         $result = $selectStmt->fetchAll();  
         if( $result[0]['capturestatus'] ) {
             $captured = true;
         }
        return $captured;
    }
    
    private function setTransactionCaptureStatus($payment, $transactionId) {
         $payment->setTransactionId($transactionId);
         $payment->setIsTransactionClosed(false);
         $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
    }
 
}