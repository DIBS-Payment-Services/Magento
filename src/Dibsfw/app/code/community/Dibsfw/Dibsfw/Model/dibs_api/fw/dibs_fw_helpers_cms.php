<?php
class dibs_fw_helpers_cms extends Mage_Payment_Model_Method_Abstract {   
    protected $_code  = 'Dibsfw';
    protected $_formBlockType = 'Dibsfw_Dibsfw_Block_Form';
    protected $_infoBlockType = 'Dibsfw_Dibsfw_Block_Info';
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
    
    public function cms_dibs_getOrderInfo() {
        $aPayInfo = array();
        $bMailing = false;
        $this->dibsflex_api_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder === NULL) {
            $oOrder = Mage::getModel('sales/order')->loadByIncrementId($_POST['orderid']);
            $bMailing = true;
        } 
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $oRead = Mage::getSingleton('core/resource')->getConnection('core_read');
                $aRow = $oRead->fetchRow("SELECT `status`, `transact`, `paytype`, `fee` FROM `" . 
                                         Mage::getConfig()->getTablePrefix() . 
                                        "dibs_orderdata` WHERE `orderid` = " . $iOid . " LIMIT 1;");
                if(count($aRow) > 0) {
                    if($aRow['status'] == '1') {
                        if($aRow['transact'] != '0') {
                            $aPayInfo[Mage::helper('dibsfw')->__('DIBSFW_LABEL_8')] = $aRow['transact'];
                        }
                       
                        if($bMailing === FALSE) {
                            if($aRow['paytype'] != '0') {
                                $aPayInfo[Mage::helper('dibsfw')->__('DIBSFW_LABEL_12')] = $aRow['paytype'];
                            }
                        
                            if($aRow['fee'] != '0') {
                                $aPayInfo[Mage::helper('dibsfw')->__('DIBSFW_LABEL_11')] = 
                                            $oOrder->getOrderCurrencyCode() . "&nbsp;" . 
                                            number_format(((int) $aRow['fee']) / 100, 2, ',', ' ');
                            }
                        }
                    }
                    else $aPayInfo[Mage::helper('dibsfw')->__('DIBSFW_LABEL_25')] = Mage::helper('dibsfw')->__('DIBSFW_LABEL_19');
                }
            }
        }
        
        return $aPayInfo;
    }    
    
    public function cms_dibs_getAdminOrderInfo() {
        $res = array();
        $this->dibsflex_api_checkTable();
        $oOrder = Mage::registry('current_order');
        if($oOrder !== NULL &&  is_callable(array($oOrder, 'getIncrementId'))) {
            $iOid = $oOrder->getIncrementId();
            if(!empty($iOid)) {
                $sButtons = $this->dibsflex_api_cgibuttons($iOid);
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $row = $read->fetchRow("SELECT * FROM " . Mage::getConfig()->getTablePrefix() . 
                                       "dibs_orderdata WHERE orderid = " . $iOid . " LIMIT 1;");

                if(count($row) > 0) {
                    if($row['status'] == '1') {
                        if($row['transact'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_8')] = $row['transact'];
                        }

                        if($row['amount'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_9')] = $oOrder->getOrderCurrencyCode() . 
                                    "&nbsp;" . number_format(((int) $row['amount']) / 100, 2, ',', ' ');
                        }

                        if($row['currency'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_10')] = $row['currency'];
                        }

                        if($row['fee'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_11')] = $oOrder->getOrderCurrencyCode() . 
                                    "&nbsp;" . number_format(((int) $row['fee']) / 100, 2, ',', ' ');
                        }

                        if($row['paytype'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_12')] = $row['paytype'];
                        }
                    
                        if($row['cardnomask'] != '0' && $row['cardprefix'] == '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_13')] = 
                                    "............" . trim($row['cardnomask'], 'X');
                        }

                        if($row['cardprefix'] != '0' && $row['cardnomask'] == '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_13')] = 
                                    $row['cardprefix'] . "..........";
                        }

                        if($row['cardprefix'] != '0' && $row['cardnomask'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_13')] = 
                                    (substr($row['cardnomask'], 0, 6) === $row['cardprefix']) ?
                                    str_replace("X", ".", $row['cardnomask']) :
                                    $row['cardprefix'] . '......' . trim($row['cardnomask'], 'X');
                        }

                        if($row['cardexpdate'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_14')] = 
                                substr($row['cardexpdate'], 2, 2) . " / " . substr($row['cardexpdate'], 0, 2);
                        }

                        if($row['cardcountry'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_15')] = $row['cardcountry'];
                        }

                        if($row['acquirer'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_16')] = $row['acquirer'];
                        }

                        if($row['enrolled'] != '0') {
                            $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_17')] = $row['enrolled'];
                        }

                        $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_26')] = $sButtons;
                        $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_25')] = Mage::helper('dibsfw')->__('DIBSFW_LABEL_18') . 
                                ': <a href="https://payment.architrade.com/admin/">https://payment.architrade.com/admin/</a>';
                    }
                    else $res[Mage::helper('dibsfw')->__('DIBSFW_LABEL_25')] = Mage::helper('dibsfw')->__('DIBSFW_LABEL_19');
                }
            }
        }
        
        return $res;
    }

    public function cms_get_imgHtml($sLogo) {
        $sImgUrl = Mage::getDesign()->getSkinUrl('images/Dibsfw/Dibsfw/' . 
                                                 preg_replace("/(\(|\)|_)/s", "",
                                                 strtolower($sLogo)) . '.gif');
        return (file_exists("." . strstr($sImgUrl, "/skin/"))) ?
               '<img src="' . $sImgUrl . '" alt="' . htmlentities($sLogo) . '" />' : "";
    }
    
    public function setOrderStatusAfterPayment(){
	$order = Mage::getModel('sales/order');
	$order->loadByIncrementId($_POST['orderid']);
        $order->setState($this->getConfigData('order_status_after_payment'),
                         true,
                         Mage::helper('dibsfw')->__('DIBSFW_LABEL_22'));

	$order->save();
    }
    
    // Remove from stock (if used)
    public function removeFromStock() {
    	// Load the session object
      	$session = Mage::getSingleton('checkout/session');
     	$session->setDibsfwStandardQuoteId($session->getQuoteId());
      
      	// Load the order object
	$order = Mage::getModel('sales/order');
	$order->loadByIncrementId($_POST['orderid']);
      
        // remove items from stock
        // http://www.magentocommerce.com/wiki/groups/132/protx_form_-_subtracting_stock_on_successful_payment
        if (((int)$this->getConfigData('handlestock')) == 1) {
            $items = $order->getAllItems(); // Get all items from the order
            if ($items) {
                foreach($items as $item) {
                    $quantity = $item->getQtyOrdered(); // get Qty ordered
                    $product_id = $item->getProductId(); // get it's ID
                    // Load the stock for this product
                    $stock = Mage::getModel('cataloginventory/stock_item')
                             ->loadByProduct($product_id);
                    $stock->setQty($stock->getQty()-$quantity); // Set to new Qty            
                    $stock->save(); // Save
                    continue;                        
                }
            }
        }
    }
}
?>
