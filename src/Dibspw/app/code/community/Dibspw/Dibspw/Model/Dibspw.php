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
	     
    protected $_canReviewPayment = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_isInitializeNeeded = true;

    /**
     * Payment method code
     *
     * @var string
     */
 
    
      public function authorize(Varien_Object $payment, $amount) {
        return $this;
    }
  


     
    /* 
     * Validate the currency code is avaialable to use for dibs or not
     */
    public function validate() {
        parent::validate();
        $sCurrencyCode = Mage::getSingleton('checkout/session')->getQuote()->getBaseCurrencyCode();
        if (!array_key_exists($sCurrencyCode, dibs_pw_api::api_dibs_get_currencyArray())) {
            Mage::throwException(Mage::helper('Dibspw')->__('Selected currency code (' . 
                                                $sCurrencyCode . ') is not compatabile with Dibs'));
        }
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
        $result = $this->callDibsApi($payment, $amount, 'CaptureTransaction');
        
        switch ($result['status']) {
            case 'ACCEPT':
                    $payment->setTransactionId($result['transaction_id']);
                    $payment->setIsTransactionClosed(false);
                    $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
                break;
            case 'DECLINE':
                    $errorMsg = $this->_getHelper()->__("DEBS returned DECLINE check your payment in DIBS admin. Error msg: ".$result['message']);
                    $this->log("Capture DECLINE. Error message:".$result['message']); 
                break;
            case 'ERROR':
                     $errorMsg = $this->_getHelper()->__("DIBS returned ERROR check your payment in DIBS admin. Error msg: ".$result['message']);
                     $this->log("Capture ERROR. Error message:".$result['message'], $result['transaction_id']);   
                break;
            case 'PENDING':
                     $errorMsg = $this->_getHelper()->__("DIBS returned PENDING check your payment in DIBS admin later");
                     $this->log("Capture PENDING. Error message:".$result['message'], $result['transaction_id']); 
                break;
                default:
                    $errorMsg = $this->_getHelper()->__("Error due online capture" . $result['message']);
                    $this->log("Capture uncnown error. Error message:".$result['message'], $result['transaction_id']); 
                break;
        }
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
       $result = $this->callDibsApi($payment,$amount,'RefundTransaction');
         switch ($result['status']) {
            case 'ACCEPT':
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_APPROVED);
            break;
     
            case 'ERROR' :
                $errorMsg = $this->_getHelper()->__("Error due online refund" . $result['message']);
                $this->log("Refund ERROR. Error message:".$result['message'], $result['transaction_id']);   
            break;      
            
            case 'DECLINE' :
                $errorMsg = $this->_getHelper()->__("Refund attempt was DECLINED" . $result['message']);
                $this->log("Refund DECLINE. Error message:".$result['message'], $result['transaction_id']);   
            break;    
         }
        if($errorMsg){
            Mage::throwException($errorMsg);
        }
       return $this;
    }
    
    
    public function cancel(Varien_Object $payment) {
    
       $result = $this->callDibsApi($payment,$amount,'CancelTransaction');
       switch ($result['status']) {
           case 'ACCEPT':
                $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_VOID);
           break;
     
           case 'ERROR' :
               $errorMsg = $this->_getHelper()->__("Error due online cancel. Use DIBS Admin panel to manually cancel this transaction" . $result['message']);
               $this->log("Cancel ERROR. Error message:".$result['message'], $result['transaction_id']);   
           break;      
           
           case 'DECLINE' :
                $errorMsg = $this->_getHelper()->__("Cancel was DECLINED. Use DIBS Admin panel to manually cancel this transaction" . $result['message']);
                $this->log("Cancel DECLINE. Error message:".$result['message'], $result['transaction_id']);   
           break;
           
           default:
                $errorMsg = $this->_getHelper()->__("Uncnown error was occured. Use DIBS Admin panel to manually cancel this transaction" . $result['message']);
                $this->log("Cancel uncnown error. Error message:".$result['message'], $result['transaction_id']);   
           break;
        }
         
       if($errorMsg){
           Mage::throwException($errorMsg);
       }
        return $this;
    }
    
    
    private function log($msg, $transaction) {
         $msg = array('message' => $msg, 'transaction' => $transaction);
         Mage::log($msg, null, 'dibs_pw.log');
    }
    
}