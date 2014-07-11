<?php
class dibs_pw_helpers_cms extends Mage_Payment_Model_Method_Abstract {   
    protected $_code  = 'Dibspw';
    protected $_formBlockType = 'Dibspw_Dibspw_Block_Form';
    protected $_infoBlockType = 'Dibspw_Dibspw_Block_Info';
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    
    public function cms_dibs_getOrderInfo() {
        $aPayInfo = array();
        $bMailing = false;
        $this->api_dibs_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder === NULL) {
            if(isset($_POST['orderid'])) {
            $oOrder = Mage::getModel('sales/order')->loadByIncrementId($_POST['orderid']);
            $bMailing = true;
	    }			
        } 
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
                $aRow = $oRead->fetchRow("SELECT `status`, `transaction`, `paytype`, `fee` FROM `" . 
                                         Mage::getConfig()->getTablePrefix() . 
                                         dibs_pw_api::api_dibs_get_tableName() .
                                        "` WHERE `orderid` = " . $iOid . " LIMIT 1;");
                if(count($aRow) > 0) {
                    if($aRow['status'] == 'ACCEPTED') {
                        if($aRow['transaction'] != '0') {
                            $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_8')] = $aRow['transaction'];
                        }
                       
                        if($bMailing === FALSE) {
                            if(!empty($aRow['paytype'])) {
                                $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_12')] = $aRow['paytype'];
                            }
                        
                            if(!empty($aRow['fee'])) {
                                $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_11')] = 
                                            $oOrder->getOrderCurrencyCode() . "&nbsp;" . 
                                            number_format(((int) $aRow['fee']) / 100, 2, ',', ' ');
                            }
                        }
                    }
                    else $aPayInfo[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_19');
                }
            }
        }
        
        return $aPayInfo;
    }    
    
    public function cms_dibs_getAdminOrderInfo() {
        $res = array();
        $this->api_dibs_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $row = $read->fetchRow("SELECT `status`, `transaction`, `amount`, `currency`, `fee`, 
                                `paytype`, `acquirerFirstName`,`acquirerLastName`, `acquirerDeliveryAddress`,
                                `acquirerDeliveryPostalCode`, `acquirerDeliveryCountryCode`, `acquirerDeliveryPostalPlace` ,`ext_info` FROM " . Mage::getConfig()->getTablePrefix() .
                                dibs_pw_api::api_dibs_get_tableName() . "
                                WHERE orderid = " . $iOid . " LIMIT 1;");

                if(count($row) > 0) {
                    if($row['status'] == 'ACCEPTED') {
                        $row['ext'] = (isset($row['ext_info']) && $row['ext_info'] != NULL) ?
                                      unserialize($row['ext_info']) : array();
                        
                        if(!empty($row['transaction'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_8')] = $row['transaction'];
                        }

                        if(!empty($row['amount'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_9')] = $oOrder->getOrderCurrencyCode() . 
                                    "&nbsp;" . number_format(((int) $row['amount']) / 100, 2, ',', ' ');
                        }

                        if(!empty($row['currency'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_10')] = $row['currency'];
                        }

                        if(!empty($row['fee'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_11')] = $oOrder->getOrderCurrencyCode() . 
                                    "&nbsp;" . number_format(((int) $row['fee']) / 100, 2, ',', ' ');
                        }
                      
                        if(!empty($row['paytype'])) {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_12')] = $row['paytype'];
                        }
                    
                        if($row['ext']['acquirer'] != '0') {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_16')] = $row['ext']['acquirer'];
                        }

                        if( isset($row['ext']['enrolled']) && $row['ext']['enrolled'] != '0') {
                            $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_17')] = $row['ext']['enrolled'];
                        }

                        $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_18') . 
                                ': <a href="https://payment.architrade.com/admin/">https://payment.architrade.com/admin/</a>';
                                
                        
                        if(!empty($row['acquirerFirstName'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_31")] = $row['acquirerFirstName'];
                        }       
                        
                        
                        if(!empty($row['acquirerLastName'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_30")] = $row['acquirerLastName'];
                        }       
                                
                      
                        if(!empty($row['acquirerDeliveryAddress'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_32")] = $row['acquirerDeliveryAddress'];
                        }       
                       
                         
                        if(!empty($row['acquirerDeliveryCountryCode'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_36")] = $row['acquirerDeliveryCountryCode'];
                        }    
                        
                        
                        if(!empty($row['acquirerDeliveryPostalCode'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_33")] = $row['acquirerDeliveryPostalCode'];
                        }  
                        
                        
                        if(!empty($row['acquirerDeliveryPostalPlace'])) {
                            $res[Mage::helper('dibspw')->__("DIBSPW_LABEL_34")] = $row['acquirerDeliveryPostalPlace'];
                        }           
                       
                        
                    }
                    else $res[Mage::helper('dibspw')->__('DIBSPW_LABEL_25')] = Mage::helper('dibspw')->__('DIBSPW_LABEL_19');
                }
            }
        }
       // $res = array();
        return $res;
    }
    
    public function cms_get_imgHtml($sLogo) {
        $sImgUrl = Mage::getDesign()->getSkinUrl('images/Dibspw/Dibspw/' . 
                                                 preg_replace("/(\(|\)|_)/s", "",
                                                 strtolower($sLogo)) . '.gif');
        return (file_exists("." . strstr($sImgUrl, "/skin/"))) ?
               '<img src="' . $sImgUrl . '" alt="' . htmlentities($sLogo) . '" />' : "";
    }

    public function setOrderStatusAfterPayment(){
	   $status = $_POST['status'];
       $infoMessage = "";
       switch($status) {
        case "ACCEPTED":
           $infoMessage = 'DIBSPW_LABEL_28';
         break;
        case "PENDING":
           $infoMessage = 'DIBSPW_LABEL_27';
         break;
        case "DECLINED":
           $infoMessage = 'DIBSPW_LABEL_29';
         break;
       }

       $infoMessage = Mage::helper('dibspw')->__($infoMessage); 
       $order = Mage::getModel('sales/order');
	   $order->loadByIncrementId($_POST['orderid']);
        
        if($status == "ACCEPTED") {
       $order->setState($this->getConfigData('order_status_after_payment'),
                         true,
                         $infoMessage);
        // Add fee to sales_order_table, if order has fee
         if( isset($_POST['fee']) && $_POST['fee']) {
             $order->setData('fee_amount', $_POST['fee']);
         }
        } else {
          $order->addStatusHistoryComment($infoMessage);  
        }  
	    $order->save();
        
    }
    
   /**
    * Removes items from stock, depends on 'handlestock' module configuration option
    * (used for successful payments)
    * 
    * http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
    */
    public function removeFromStock($iOrderId) {
      	$oSession = Mage::getSingleton('checkout/session');
     	$oSession->setDibspwStandardQuoteId($oSession->getQuoteId());
      
	$oOrder = Mage::getModel('sales/order');
	$oOrder->loadByIncrementId($iOrderId);

        if (((int)$this->getConfigData('handlestock')) == 1) {
            $oItems = $oOrder->getAllItems();
            if ($oItems) {
                foreach($oItems as $oItem) {
                    $oStock = Mage::getModel('cataloginventory/stock_item')
                                 ->loadByProduct($oItem->getProductId());
                    $oStock->setQty($oStock->getQty() - $oItem->getQtyOrdered());
                    $oStock->save();
                    continue;                        
                }
            }
        }
    }
}
?>