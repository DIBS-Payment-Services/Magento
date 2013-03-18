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

/**
 * Payment Controller
 **/

class Dibspw_Dibspw_DibspwController extends Mage_Core_Controller_Front_Action {

    private $oDibsModel;
    
    /**
     * Constructor aggrigates module's model to controller's field.
     * (not a real constructor, Magento calls it automatically when it's needed)
     */
    function _construct() {
        $this->oDibsModel= Mage::getModel('dibspw/Dibspw');
    }

    /**
     * Prepares redirect page data, add payment status information to order.
     * Renders view to redirect customer.
     * Adds comment to order history if it's not added before.
     */
    public function redirectAction(){
      	$oSession = Mage::getSingleton('checkout/session');
      	$oSession->setDibspwQuoteId($oSession->getQuoteId());

        $oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId($oSession->getLastRealOrderId());
        
        $this->loadLayout();
        if($oOrder->getPayment() !== FALSE) {
            // Create the POST to DIBS (Inside Magento Checkout)
            $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('dibspw/redirect'));
                
            // Create the POST to DIBS (In Separate "Blank" Window)
            // $this->getResponse()->setBody($this->getLayout()->createBlock('Dibspw/redirect')->toHtml());
      
            // Save order comment if it's not already saved.
            $sOrderComment = "";
            foreach($oOrder->getAllStatusHistory() as $oOrderStatusItem) {
                $sOrderComment = $oOrderStatusItem->getComment();
                break;
            }
            
            if($sOrderComment != $this->__('DIBSPW_LABEL_3')) {
                $oOrder->addStatusToHistory($oOrder->getStatus(), $this->__('DIBSPW_LABEL_3'));
            }
            
            $oOrder->save();
            // Add items back on stock (if used)
            $this->addToStock();
        }
        else $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('dibspw/failure'));
        $this->renderLayout();
    }
    
    /**
     * Processes success action with system API (data check, saving and order update)
     * Redirects customer to appropriate view (Magento success page or order verification error page)
     * Handle stock on success.
     */
    public function successAction() {
        $oSession = Mage::getSingleton('checkout/session');
        $oSession->setQuoteId($oSession->getDibspwStandardQuoteId(true));
        
        $iOrderId = (int)$this->getRequest()->getPost('orderid');
        if(!empty($iOrderId)) {
            $oOrder = Mage::getModel('sales/order');
            $oOrder->loadByIncrementId($iOrderId);
            if(!is_null($oOrder)) {
                $iResult = $this->oDibsModel->api_dibs_action_success($oOrder);
                if(!empty($iResult)) {
                    echo $this->oDibsModel->api_dibs_getFatalErrorPage($iResult);
                    exit;
                }
                else {
                    Mage::app()->getFrontController()->getResponse()->setRedirect(
                        $this->oDibsModel->helper_dibs_tools_url('checkout/onepage/success')
                    );
                }
            }
            else {
                echo $this->oDibsModel->api_dibs_getFatalErrorPage(2);
                exit;            
            }
        }
        else {
            echo $this->oDibsModel->api_dibs_getFatalErrorPage(1);
            exit;
        }
    }
    
    /**
     * Processes server-to-server callback with system API.
     * API checks order data and adds additional information about order to module's transactions 
     * log table.
     */
    public function callbackAction() {
        $oOrder = Mage::getModel('sales/order');
        $this->oDibsModel->api_dibs_action_callback($oOrder);
    }
    
    /**
     * Processes order cancelation with Magento API and module's API.
     * sets appropriate status and order history comment.
     * Redirects customer to orders history view.
     */
    public function cancelAction() {
    	$oSession = Mage::getSingleton('checkout/session');
        $oSession->setQuoteId($oSession->getDibspwStandardQuoteId(true));

        $iOrderId = (int)$this->getRequest()->getPost('orderid');
      	$oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId($iOrderId);
        
	if(!is_null($oOrder)) {
            $oOrder->registerCancellation($this->__('DIBSPW_LABEL_20'));
            $oOrder->save();
            $this->oDibsModel->removeFromStock($iOrderId);
            $this->oDibsModel->api_dibs_action_cancel();
        }
        
        Mage::app()->getFrontController()->getResponse()->setRedirect(
                $this->oDibsModel->helper_dibs_tools_url('sales/order/history')
        );
    }
    
    /**
     * Checks Magento ajax session expiration status, sets appropriate header.
     */
    protected function _expireAjax(){
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Returns items to stock, depends on 'handlestock' module configuration option
     * (needed to fix behavior, when Magento can decrease stock automatically before order is paid)
     *
     * Put the order back on stock as it is not yet paid!
     * http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
     */
    public function addToStock() {
      	$oSession = Mage::getSingleton('checkout/session');
      	$oSession->setDibspwStandardQuoteId($oSession->getQuoteId());

        $oOrder = Mage::getModel('sales/order');
        $oOrder->loadByIncrementId($oSession->getLastRealOrderId());
      
    	if (((int)$this->oDibsModel->getConfigData('handlestock')) == 1) {
            if($oSession->getData('stock_removed') != $oSession->getLastRealOrderId()) {
                $oItems = $oOrder->getAllItems();
                if ($oItems) {
                    foreach($oItems as $oItem) {
                        $oStock = Mage::getModel('cataloginventory/stock_item')
                                    ->loadByProduct($oItem->getProductId());
                        $oStock->setQty($oStock->getQty() + $oItem->getQtyOrdered());
                        $oStock->save();
                        continue;                        
                    }
                } 

                $oSession->setData('stock_removed', $oSession->getLastRealOrderId());
            }
        }
    }
}