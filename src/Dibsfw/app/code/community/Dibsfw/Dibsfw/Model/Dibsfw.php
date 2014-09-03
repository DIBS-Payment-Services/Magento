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

//require_once str_replace("\\", "/", dirname(__FILE__)) . '/dibs_api/fw/dibs_fw_api.php';
require_once  Mage::getBaseDir('code').'/community/Dibsfw/Dibsfw/Model/dibs_api/fw/dibs_fw_api.php';

class Dibsfw_Dibsfw_Model_Dibsfw extends dibs_fw_api {

    /* 
     * Validate the currency code is avaialable to use for dibs or not
     */
    public function validate() {
        parent::validate();
        $sCurrencyCode = Mage::getSingleton('checkout/session')->getQuote()->getBaseCurrencyCode();
        if (!array_key_exists($sCurrencyCode, $this->dibsflex_api_getCurrencyArray())) {
            Mage::throwException(Mage::helper('dibsfw')->__('Selected currency code (' . 
                                                $sCurrencyCode . ') is not compatabile with Dibs'));
        }
        return $this;
    }
    
    public function getCheckoutFormFields() {
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $aFields = $this->dibsflex_api_requestModel($order);
        
        return $aFields;
    }
    
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('Dibsfw/Dibsfw/redirect', array('_secure' => true));
    }
    
    
    public function cancelOrdersAfterTimeout() {
        $definedTimeout = $this->dibsflex_helper_getconfig('timeout');
        if ($definedTimeout < 0) {
            return $this;
        }
        
        $timeout = date('Y-m-d H:i:s', time()-($definedTimeout*60));
     
        $orders = Mage::getModel('sales/order')->getCollection()->
            join(array('op' => 'sales/order_payment'), 'op.entity_id = main_table.entity_id', 'method')->
            addAttributeToFilter('method', array('eq' => 'Dibsfw'))
            ->addFieldToFilter('updated_at', array('lt' => $timeout))
            ->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT))
            ->setPageSize(25);
        
        foreach ($orders as $order) {
            $order->cancel()->save();
        }
        return $this;
    }
}