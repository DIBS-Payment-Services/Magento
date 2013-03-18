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

    /* 
     * Validate the currency code is avaialable to use for dibs or not
     */
    public function validate() {
        parent::validate();
        $sCurrencyCode = Mage::getSingleton('checkout/session')->getQuote()->getBaseCurrencyCode();
        if (!array_key_exists($sCurrencyCode, dibs_pw_api::api_dibs_get_currencyArray())) {
            Mage::throwException(Mage::helper('dibspw')->__('Selected currency code (' . 
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
}