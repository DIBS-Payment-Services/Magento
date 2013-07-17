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
    
    function dibsflex_helper_getOrderObj($mOrderInfo, $bResponse = FALSE) {
        if($bResponse === TRUE) $mOrderInfo->loadByIncrementId((int)$_POST['orderid']);
        
        return (object)array(
            'order_id'  => $mOrderInfo->getRealOrderId(),
            'total'     => $this->dibsflex_api_float2intSmartRounding($mOrderInfo->getTotalDue()),
            'currency'  => $this->dibsflex_api_getCurrencyValue(
                               $mOrderInfo->getOrderCurrency()->getCode()
                           )
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

    function dibsflex_helper_getShippingObj($mOrderInfo) {
        return (object)array(
                'method' => $mOrderInfo['shipping_description'],
                'rate'   => $this->dibsflex_api_float2intSmartRounding($mOrderInfo['shipping_amount']),
                'tax'    => isset($mOrderInfo['shipping_tax_amount']) ? 
                            $this->dibsflex_api_float2intSmartRounding($mOrderInfo['shipping_tax_amount']) : 0
            );
    }

    function dibsflex_helper_getItemsObj($mOrderInfo) {
        foreach($mOrderInfo->getAllItems() as $oItem) {
            $oItems[] = (object)array(
                'item_id'   => $oItem->getProductId(),
                'name'      => $oItem->getName(),
                'sku'       => $oItem->getSku(),
                'price'     => $this->dibsflex_api_float2intSmartRounding($oItem->getPrice()),
                'qty'       => $this->dibsflex_api_float2intSmartRounding($oItem->getQtyOrdered(), 3),
                'tax_rate'  => $this->dibsflex_api_float2intSmartRounding($oItem->getTaxAmount() / 
                                                                          $oItem->getQtyOrdered())
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
        return 'mgn1_3.0.6';
    }
}
?>