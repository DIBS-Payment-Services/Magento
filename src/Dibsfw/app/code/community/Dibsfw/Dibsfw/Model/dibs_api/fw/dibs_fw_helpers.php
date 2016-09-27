<?php
class dibs_fw_helpers extends dibs_fw_helpers_cms implements dibs_fw_helpers_interface {

    function dibsflex_helper_dbquery_write($sQuery) {
        $oWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $oWrite->query($sQuery);
    }
    
    function dibsflex_helper_dbquery_read($sQuery) {
	$oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        return $oRead->fetchRow($sQuery);
    }
    
    function dibsflex_helper_dbquery_read_single($mResult, $sName) {
        return (isset($mResult[$sName])) ? $mResult[$sName] : null;
    }
    
    function dibsflex_helper_cmsurl($sLink) {
        return Mage::getUrl($sLink, array('_secure' => true));
    }
    
    function dibsflex_helper_getconfig($sVar, $sPrefix = 'DIBSFW_') {
        return (($sVar == 'apiuser' || $sVar == 'apipass') && 
               is_callable(array(Mage::registry('current_order'), 'getStoreId'))) ?
               $this->getConfigData($sPrefix . $sVar, Mage::registry('current_order')->getStoreId()) :
               $this->getConfigData($sPrefix . $sVar);
    }
    
    function dibsflex_helper_getdbprefix() {
        return Mage::getConfig()->getTablePrefix();
    }
    
    function dibsflex_helper_getReturnURLs($sURL) {

        switch ($sURL) {
            case 'success':
                return $this->dibsflex_helper_cmsurl("Dibsfw/Dibsfw/success");
            break;
            case 'callback':
                return $this->dibsflex_helper_cmsurl("Dibsfw/Dibsfw/callback");
            break;
            case 'callbackfix':
                return $this->dibsflex_helper_cmsurl("Dibsfw/Dibsfw/callback");
            break;
            case 'cgi':
                return $this->dibsflex_helper_cmsurl("Dibsfw/Dibsfw/cgiapi");
            break;
            case 'cancel':
                return $this->dibsflex_helper_cmsurl("Dibsfw/Dibsfw/cancel");
            break;
            default:
                return $this->dibsflex_helper_cmsurl("customer/account/index");
            break;
        }
    }
    /**
     * https://en.wikipedia.org/wiki/ISO_4217
     * @var array
     */
    public $currenciesWithNotTwoSings = array(   '392' => 0, '048' => 3, '108' => 0, 
                                                 '974' => 0, '990' => 4, '152' => 0, 
                                                 '132' => 0, '262' => 0, '324' => 0,
                                                 '368' => 3, '352' => 0, '400' => 3,
                                                 '174' => 0, '410' => 0, '414' => 3,
                                                 '434' => 3, '512' => 3, '600' => 0,
                                                 '646' => 0, '788' => 3, '800' => 0,
                                                 '940' => 0, '704' => 0, '548' => 0,
                                                 '950' => 0, '952' => 0, '953' => 0);
    
    
    function dibsflex_helper_getOrderObj($mOrderInfo, $bResponse = FALSE) {
        if($bResponse === TRUE) $mOrderInfo->loadByIncrementId((int)$_POST['orderid']);
        
        $currencyCode = $this->dibsflex_api_getCurrencyValue(
                               $mOrderInfo->getOrderCurrency()->getCode());
        return (object)array(
            'order_id'  => $mOrderInfo->getRealOrderId(),
            'total'     => $this->dibsflex_api_float2intSmartRounding($mOrderInfo->getTotalDue(), $this->dibsflex_helper_getCurrencyDecimals($currencyCode)),
            'currency'  => $currencyCode
        );
    }
    
    function dibsflex_helper_getAddressObj($mOrderInfo) {
        $aShipping = $mOrderInfo->getShippingAddress();
        $aBilling  = $mOrderInfo->getBillingAddress();
        
        return (object)array(
                'billing'   => (object)array(
                    'firstname' => $aBilling['firstname'],
                    'lastname'  => $aBilling['lastname'],
                    'street'    => $aBilling['street'],
                    'postcode'  => $aBilling['postcode'],
                    'city'      => $aBilling['city'],
                    'region'    => $aBilling['region'],
                    'country'   => $aBilling['country_id'],
                    'phone'     => $aBilling['telephone'],
                    'email'     => $mOrderInfo['customer_email']
                ),
                'delivery'  => (object)array(
                    'firstname' => $aShipping['firstname'],
                    'lastname'  => $aShipping['lastname'],
                    'street'    => $aShipping['street'],
                    'postcode'  => $aShipping['postcode'],
                    'city'      => $aShipping['city'],
                    'region'    => $aShipping['region'],
                    'country'   => $aShipping['country_id'],
                    'phone'     => $aShipping['telephone'],
                    'email'     => $mOrderInfo['customer_email']
                )
            );
    }
    
    function dibsflex_helper_getCurrencyDecimals($currencyValue) {
        if(key_exists($currencyValue, $this->currenciesWithNotTwoSings)) {
            return $this->currenciesWithNotTwoSings[$currencyValue];
        }
        // for the moost currencies
        return 2;
    }

    function dibsflex_helper_getShippingObj($mOrderInfo) {
        $currencyValue = $this->dibsflex_api_getCurrencyValue($mOrderInfo->getOrderCurrencyCode());
        return (object)array(
                'method' => $mOrderInfo['shipping_description'],
                'rate'   => $this->dibsflex_api_float2intSmartRounding($mOrderInfo['shipping_amount'], $this->dibsflex_helper_getCurrencyDecimals($currencyValue)),
                'tax'    => isset($mOrderInfo['shipping_tax_amount']) ? 
                            $this->dibsflex_api_float2intSmartRounding($mOrderInfo['shipping_tax_amount'], $this->dibsflex_helper_getCurrencyDecimals($currencyValue)) : 0
            );
    }

    function dibsflex_helper_getItemsObj($mOrderInfo) {
        $currencyValue = $this->dibsflex_api_getCurrencyValue($mOrderInfo->getOrderCurrencyCode());
        foreach($mOrderInfo->getAllItems() as $oItem) {
            $oItems[] = (object)array(
                'item_id'   => $oItem->getProductId(),
                'name'      => $oItem->getName(),
                'sku'       => $oItem->getSku(),
                'price'     => $this->dibsflex_api_float2intSmartRounding($oItem->getPrice(), $this->dibsflex_helper_getCurrencyDecimals($currencyValue)),
                'qty'       => $this->dibsflex_api_float2intSmartRounding($oItem->getQtyOrdered(), 3),
                'tax_rate'  => $this->dibsflex_api_float2intSmartRounding($oItem->getTaxAmount() / 
                                                                          $oItem->getQtyOrdered(), $this->dibsflex_helper_getCurrencyDecimals($currencyValue))
            );
        }
        return $oItems;
    }

    function dibsflex_helper_redirect($sURL) {
        Mage::app()->getFrontController()->getResponse()->setRedirect($sURL);
    }

    function dibsflex_helper_getlang($sKey) {
        return Mage::helper('dibsfw')->__('dibsfw_' . $sKey);
    }
    
    function dibsflex_helper_cgiButtonsClass() {
        return 'form-button';
    }
    
    function dibsflex_helper_callbackHook($oOrder) {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getDibsfwStandardQuoteId(true));            
        if (((int)$this->dibsflex_helper_getconfig('sendmailorderconfirmation', '')) == 1) {
            $oOrder->sendNewOrderEmail();
        }
	$this->removeFromStock();
        $this->setOrderStatusAfterPayment();
        $session->setQuoteId($session->getDibsfwStandardQuoteId(true));
    }
    
    function dibsflex_helper_modVersion() {
        return 'mgn1_3.2.1';
    }
}
?>