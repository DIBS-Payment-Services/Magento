<?php
class dibs_pw_helpers extends dibs_pw_helpers_cms implements dibs_pw_helpers_interface {

    /**
     * Flag if this module uses tax amounts instead of tax percents.
     * 
     * @var bool
     */
    public static $bTaxAmount = true;
    
    /**
     * Process write SQL query (insert, update, delete) with build-in CMS ADO engine.
     * 
     * @param string $sQuery SQL query string.
     */
    function helper_dibs_db_write($sQuery) {
        $oWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        return $oWrite->query($sQuery);
    }
    
    /**
     * Read single value ($sName) from SQL select result.
     * If result with name $sName not found null returned.
     * 
     * @param string $sQuery SQL query string.
     * @param string $sName Name of field to fetch.
     * @return mixed 
     */
    function helper_dibs_db_read_single($sQuery, $sName) {
	$oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $mResult = $oRead->fetchRow($sQuery);
        return isset($mResult[$sName]) ? $mResult[$sName] : null;
    }
    
    /**
     * Return settings with CMS method.
     * 
     * @param string $sVar Variable name.
     * @param string $sPrefix Variable prefix.
     * @return string 
     */
    function helper_dibs_tools_conf($sVar, $sPrefix = 'DIBSPW_') {
        return $this->getConfigData($sPrefix . $sVar);
    }
    
    /**
     * Return CMS DB table prefix.
     * 
     * @return string 
     */
    function helper_dibs_tools_prefix() {
        return Mage::getConfig()->getTablePrefix();
    }
    
    /**
     * Returns text by key using CMS engine.
     * 
     * @param type $sKey Key of text node.
     * @param type $sType Type of text node. 
     * @return type 
     */
    function helper_dibs_tools_lang($sKey, $sType = 'msg') {
        return Mage::helper('dibspw')->__("dibspw_txt_" . $sType . "_" . $sKey);
    }

    /**
     * Get full CMS url for page.
     * 
     * @param string $sLink Link or its part to convert to full CMS-specific url.
     * @return string 
     */
    function helper_dibs_tools_url($sLink) {
        return Mage::getModel('core/url')-> getRouteUrl($sLink,null);
    }
    
    /**
     * Build CMS order information to API object.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @param bool $bResponse Flag if it's response call of this method.
     * @return object 
     */
    function helper_dibs_obj_order($mOrderInfo, $bResponse = FALSE) {
        if($bResponse === TRUE) {
            $mOrderInfo->loadByIncrementId((int)$_POST['orderid']);
        }
    
        return (object)array(
                    'orderid'  => $mOrderInfo->getRealOrderId(),
                    'amount'   => $mOrderInfo->getGrandTotal(),
                    'currency' => $mOrderInfo->getOrderCurrency()->getCode()
                                  
        );
    }
    
    /**
     * Build CMS each ordered item information to API object.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    function helper_dibs_obj_items($mOrderInfo) {
        $aItems = array();
        $spitBundle = false;
        if($this->helper_dibs_tools_conf('bundle') == 'yes') {
           $spitBundle = true;
        }
        
        foreach($mOrderInfo->getAllItems() as $oItem) {
          
            // Just exclude chilren from bundle products
            if($oItem->getParentItem()) continue;
           
            $aItems[] = (object)array(
                'id'    => $oItem->getProductId(),
                'name'  => $oItem->getName(),
                'sku'   => $oItem->getSku(),
                'price' => $oItem->getPrice(),
                'qty'   => $oItem->getQtyOrdered(),
                'tax'   => 0
            );
          
        }
        
        $fDiscount = $mOrderInfo->getDiscountAmount();
        if(!empty($fDiscount)) {
            $aItems[] = (object)array(
                'id'    => 'discount0',
                'name'  => $this->helper_dibs_tools_lang('discount_total'),
                'sku'   => '',
                'price' => $fDiscount,
                'qty'   => 1,
                'tax'   => 0
            );
        }
        
        $fTaxTotal = 0;
        $aTaxes = $mOrderInfo->getFullTaxInfo();
        foreach($aTaxes as $aTax) if(isset($aTax['amount'])) $fTaxTotal += $aTax['amount'];
       
        // Add  HiddenTaxAmount to whole Tax amount if we calculate Tax After Discount to avoid amount errors.
        if( (bool)Mage::getStoreConfig('tax/calculation/apply_after_discount', null) ) {
            $fTaxTotal +=  $mOrderInfo->getHiddenTaxAmount();
        }
        $aItems[] = (object)array(
            'id'    => 'tax0',
            'name'  => $this->helper_dibs_tools_lang('tax_total'),
            'sku'   => '',
            'price' => $fTaxTotal,
            'qty'   => 1,
            'tax'   => 0
        );
        
        return $aItems;
        
    }
    
    /**
     * Build CMS shipping information to API object.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    function helper_dibs_obj_ship($mOrderInfo) {
        return (object)array(
            'id'    => 'shipping0',
            'name'  => $this->helper_dibs_tools_lang('shipping_total'),
            'sku'   => '',
            'price' => isset($mOrderInfo['shipping_amount']) ? $mOrderInfo['shipping_amount'] : 0,
            'qty'   => 1,
            'tax'   => 0
        );
    }
    
    /**
     * Build CMS customer addresses to API object.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    function helper_dibs_obj_addr($mOrderInfo) {
        $aShipping = $mOrderInfo->getShippingAddress();
        $aBilling  = $mOrderInfo->getBillingAddress();
        
        $aShipping['street'] = str_replace("\n", " ", $aShipping['street']);
        $aBilling['street'] =  str_replace("\n", " ", $aBilling['street']);
        return (object)array(
            'shippingfirstname'  => $aShipping['firstname'],
            'shippinglastname'   => $aShipping['lastname'],
            'shippingpostalcode' => $aShipping['postcode'],
            'shippingpostalplace'=> $aShipping['city'],
            'shippingaddress2'   => $aShipping['street'],
            'shippingaddress'    => $aShipping['country_id'] . " " . 
                                    $aShipping['region'],
            'billingfirstname'   => $aBilling['firstname'],
            'billinglastname'    => $aBilling['lastname'],
            'billingpostalcode'  => $aBilling['postcode'],
            'billingpostalplace' => $aBilling['city'],
            'billingaddress2'    => $aBilling['street'],
            'billingaddress'     => $aBilling['country_id'] . " " . 
                                    $aBilling['region'],
            'billingmobile'      => $aBilling['telephone'] = str_replace( "-", "", $aBilling['telephone']),
            'billingemail'       => $mOrderInfo['customer_email']
        );
    }
    
    /**
     * Returns object with URLs needed for API, 
     * e.g.: callbackurl, acceptreturnurl, etc.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    function helper_dibs_obj_urls($mOrderInfo = null) {
        return (object)array(
                    'acceptreturnurl' => "Dibspw/Dibspw/success",
                    'callbackurl'     => "Dibspw/Dibspw/callback",
                    'cancelreturnurl' => "Dibspw/Dibspw/cancel", //"checkout/cart",
                    'carturl'         => "customer/account/index"
                );
    }
    
    /**
     * Returns object with additional information to send with payment.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    function helper_dibs_obj_etc($mOrderInfo) {
        return (object)array(
                    'sysmod'      => 'mgn1_4_2_9_4',
                    'callbackfix' => $this->helper_dibs_tools_url("Dibspw/Dibspw/callback"),
                    'partnerid'   => $this->helper_dibs_tools_conf('partnerid')
                );
    }
    
    /**
     * Hook that allows to execute CMS-specific action during callback execution.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     */
    function helper_dibs_hook_callback($oOrder) {
        $oSession = Mage::getSingleton('checkout/session');
        
        if( $_POST['status'] == "ACCEPTED" ) {
            $oSession->setQuoteId($oSession->getDibspwStandardQuoteId(true));            
            if (((int)$this->helper_dibs_tools_conf('sendmailorderconfirmation', '')) == 1) {
            // Save fee to Order object if current order has fee
            if( isset($_POST['fee']) && $_POST['fee']) {
                $oOrder->setFeeAmount($_POST['fee']);
                $oOrder->setData('fee_amount', $_POST['fee']);
                $oOrder->save();
                
            }
                $oOrder->sendNewOrderEmail();
          }
           $this->removeFromStock((int)$_POST['orderid']);
           $oSession->setQuoteId($oSession->getDibspwStandardQuoteId(true));
    } 
           $this->setOrderStatusAfterPayment();  
    
    }
}
?>