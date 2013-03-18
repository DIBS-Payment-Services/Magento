<?php
require_once str_replace("\\", "/", dirname(__FILE__)) . '/dibs_fw_helpers_interface.php';
require_once str_replace("\\", "/", dirname(__FILE__)) . '/dibs_fw_helpers_cms.php';
require_once str_replace("\\", "/", dirname(__FILE__)) . '/dibs_fw_helpers.php';

class dibs_fw_api extends dibs_fw_helpers {

    /**
     * Collects API parameters to send in dependence of checkout type
     * 
     * @param object $oOrderInfo
     * @return array 
     */
    function dibsflex_api_requestModel($mOrderInfo) {
        $oOrderInfo = $this->dibsflex_api_orderObject($mOrderInfo);
        $this->dibsflex_api_processDB($oOrderInfo->order->order_id);

        $aData = array();
        
        $this->dibsflex_api_applyCommon($aData, $oOrderInfo);
        
        $this->dibsflex_api_applyFWInvoice($aData, $oOrderInfo);
        $this->dibsflex_api_applyFlexWin($aData);
        if(method_exists($this, 'dibsflex_helper_additionalPostData')) {
            $aAdditional = $this->dibsflex_helper_additionalPostData($mOrderInfo);
            if(count($aAdditional) > 0) {
                foreach($aAdditional as $sKey => $sVal) $aData[$sKey] = $sVal;
            }
        }
        array_walk($aData, create_function('&$val', '$val = trim($val);'));
        $sMD5 = $this->dibsflex_api_calcMD5($aData);
        if($sMD5 != "") $aData['md5key'] = $sMD5;
        
        return $aData;
    }
    
    /**
     *  Calls dibsflex_api_checkTable() method and 
     *  adds orderID to dibs_orderdata table if needed
     */
    function dibsflex_api_processDB($iOrderId) {
        $this->dibsflex_api_checkTable();
        $mOrderExists = $this->dibsflex_helper_dbquery_read("SELECT COUNT(`orderid`) 
                                                    AS order_exists FROM `" . 
                                                    $this->dibsflex_helper_getdbprefix() . 
                                                    "dibs_orderdata` where `orderid` = '" . 
                                                    $iOrderId . 
                                                    "' LIMIT 1;");	

        if($this->dibsflex_helper_dbquery_read_single($mOrderExists, 'order_exists') <= 0) {
            $this->dibsflex_helper_dbquery_write("INSERT INTO `" . 
                                             $this->dibsflex_helper_getdbprefix() . 
                                             "dibs_orderdata`(`orderid`) VALUES('" . 
                                             $this->dibsflex_api_sqlEncode($iOrderId)."')");
        }
    }
    
    /**
     *  Create dibs_orderdata table if not exists
     */
    function dibsflex_api_checkTable() {
        $this->dibsflex_helper_dbquery_write("CREATE TABLE IF NOT EXISTS `" . 
                            $this->dibsflex_helper_getdbprefix() . "dibs_orderdata` (
                            `orderid` VARCHAR(45) NOT NULL DEFAULT '',
                            `transact` VARCHAR(50) NOT NULL DEFAULT '',
                            `status` INTEGER UNSIGNED NOT NULL DEFAULT 0 
                                                    COMMENT '0 = unpaid, 1 = paid',
                            `amount` VARCHAR(45) NOT NULL DEFAULT '',
                            `currency` VARCHAR(45) NOT NULL DEFAULT '',
                            `paytype` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_customerId` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_deliveryAddress` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_deliveryCountryCode` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_deliveryPostalCode` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_deliveryPostalPlace` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_firstName` VARCHAR(45) NOT NULL DEFAULT '',
                            `PBB_lastName` VARCHAR(45) NOT NULL DEFAULT '',
                            `cardnomask` VARCHAR(45) NOT NULL DEFAULT '',
                            `cardprefix` VARCHAR(45) NOT NULL DEFAULT '',
                            `cardexpdate` VARCHAR(45) NOT NULL DEFAULT '',
                            `cardcountry` VARCHAR(45) NOT NULL DEFAULT '',
                            `acquirer` VARCHAR(45) NOT NULL DEFAULT '',
                            `enrolled` VARCHAR(45) NOT NULL DEFAULT '',
                            `fee` VARCHAR(45) NOT NULL DEFAULT '',
                            `test` VARCHAR(45) NOT NULL DEFAULT '',
                            `uniqueoid` VARCHAR(45) NOT NULL DEFAULT '',
                            `approvalcode` VARCHAR(45) NOT NULL DEFAULT '',
                            `voucher` VARCHAR(45) NOT NULL DEFAULT '',
                            `amountoriginal` VARCHAR(45) NOT NULL DEFAULT '',
                            `voucheramount` VARCHAR(45) NOT NULL DEFAULT '',
                            `voucherpaymentid` VARCHAR(45) NOT NULL DEFAULT '',
                            `voucherentry` VARCHAR(45) NOT NULL DEFAULT '',
                            `voucherrest` VARCHAR(45) NOT NULL DEFAULT '',
                            `ordercancellation` INTEGER UNSIGNED NOT NULL DEFAULT 0 
                                        COMMENT '0 = NotPerformed, 1 = Performed',
                            `successaction` INTEGER UNSIGNED NOT NULL DEFAULT 0 
                                        COMMENT '0 = NotPerformed, 1 = Performed',
                            `callback` INTEGER UNSIGNED NOT NULL DEFAULT 0 
                                        COMMENT '0 = NotPerformed, 1 = Performed'
                        );"
        );
    }
    
    /**
     * Collects common API parameters to send
     * 
     * @param array $aData
     * @param object $oOrderInfo 
     */
    function dibsflex_api_applyCommon(&$aData, $oOrderInfo) {
        $aData['orderid'] = $oOrderInfo->order->order_id;
        $aData['merchant'] = $this->dibsflex_helper_getconfig('mid');
        $aData['amount'] = $oOrderInfo->order->total;
        $aData['currency'] = $oOrderInfo->order->currency;
        $aData['callbackurl'] = $this->dibsflex_helper_getReturnURLs("callback");
        $aData['callbackfix'] = $this->dibsflex_helper_getReturnURLs("callbackfix");
        
        $sAccount = $this->dibsflex_helper_getconfig('account');
        if((string)$sAccount != "") {
            $aData['account'] = $sAccount;
        }
        
	$sPaytype = $this->dibsflex_helper_getconfig('paytype');
        if((string)$sPaytype != '') {
            $aData['paytype'] = $this->dibsflex_api_getPaytype($sPaytype);
        }
        
        $sDistributionType = $this->dibsflex_helper_getconfig('distr');
        if((string)$sDistributionType != 'empty') {
            $aData['distributionType'] = $sDistributionType;
            if ($sDistributionType == 'email'){
            	$aData['email'] = $oOrderInfo->customer->billing->email;
            }
	}
    }
    
    /**
     * Collects FlexWin Invoice API parameters to send
     * 
     * @param array $aData
     * @param object $oOrderInfo 
     */
    function dibsflex_api_applyFWInvoice(&$aData, $oOrderInfo) {
        
        $aData ['delivery01.Billing'] = 'Billing Address';
        $aData ['delivery02.Firstname'] = $oOrderInfo->customer->billing->firstname;
        $aData ['delivery03.Lastname'] = $oOrderInfo->customer->billing->lastname;
        $aData ['delivery04.Street'] = $oOrderInfo->customer->billing->street;
        $aData ['delivery05.Postcode'] = $oOrderInfo->customer->billing->postcode;
        $aData ['delivery06.City'] = $oOrderInfo->customer->billing->city;
        $aData ['delivery07.Region'] = $oOrderInfo->customer->billing->region;
        $aData ['delivery08.Country'] = $oOrderInfo->customer->billing->country;
        $aData ['delivery09.Telephone'] = $oOrderInfo->customer->billing->phone;
        $aData ['delivery10.E-mail'] = $oOrderInfo->customer->billing->email;
	
        $aData ['delivery11.Delivery'] = 'Shipping Address';
        $aData ['delivery12.Firstname'] = $oOrderInfo->customer->delivery->firstname;
        $aData ['delivery13.Lastname'] = $oOrderInfo->customer->delivery->lastname;
        $aData ['delivery14.Street'] = $oOrderInfo->customer->delivery->street;
        $aData ['delivery15.Postcode'] = $oOrderInfo->customer->delivery->postcode;
        $aData ['delivery16.City'] = $oOrderInfo->customer->delivery->city;
        $aData ['delivery17.Region'] = $oOrderInfo->customer->delivery->region;
        $aData ['delivery18.Country'] = $oOrderInfo->customer->delivery->country;
        $aData ['delivery19.Telephone'] = $oOrderInfo->customer->delivery->phone;

        if ($oOrderInfo->items) {
            $aData ['ordline0-1'] = 'ItemID  ';
            $aData ['ordline0-2'] = 'ItemDescription        ';
            $aData ['ordline0-3'] = 'SKU   ';
            $aData ['ordline0-4'] = 'Price  ';
            $aData ['ordline0-5'] = 'Tax    ';
            $aData ['ordline0-6'] = 'Quantity   ';
            $aData ['ordline0-7'] = 'TotalPrice    ';
            
            $i = 1;
            foreach($oOrderInfo->items as $oItem) {
                $aData ['ordline'.$i.'-1'] = $oItem->item_id;
		$aData ['ordline'.$i.'-2'] = $this->dibsflex_api_utf8Fix($oItem->name);
		$aData ['ordline'.$i.'-3'] = $this->dibsflex_api_utf8Fix($oItem->sku);
		$aData ['ordline'.$i.'-4'] = round($oItem->price / 100, 2);
		$aData ['ordline'.$i.'-5'] = round(($oItem->qty / 1000) * 
                                                   ($oItem->price / 100) *
                                                   ($oItem->tax_rate / 10000), 2);
		$aData ['ordline'.$i.'-6'] = round($oItem->qty / 1000);
		$aData ['ordline'.$i.'-7'] = round(($oItem->price / 100) * 
                                                   ($oItem->qty / 1000) *
                                                   ($oItem->tax_rate / 10000 + 1), 2);

                $i++;
            }
            
            $aData ['priceinfo1.Shippingmethod'] = $oOrderInfo->shipping->method;
            $aData ['priceinfo2.Shippingcost'] = round($oOrderInfo->shipping->rate / 100, 2);
	}
        
        $aData['structuredOrderInformation'] = $this->dibsflex_api_getInvoiceXML($oOrderInfo);
    }
    
    /**
     * Collects FlexWin API parameters to send
     * 
     * @param array $aData 
     */
    function dibsflex_api_applyFlexWin(&$aData) {
        $aData['accepturl'] = $this->dibsflex_helper_getReturnURLs('success');
	$aData['cancelurl'] = $this->dibsflex_helper_getReturnURLs('cancel');
        $aData['lang']      = $this->dibsflex_helper_getconfig('lang');
        $aData['sysmod']    = $this->dibsflex_helper_modVersion();
        $sSkiplastpage      = $this->dibsflex_helper_getconfig('skiplast');
        $aData['doNotShowLastPage'] = "true"; /* For PBB at Gothia */
        if((string)$sSkiplastpage == 'yes') {
            $aData['skiplastpage'] = 1;
        }
          
        $sSendCookies = $this->dibsflex_helper_getconfig('sendcookies');
        if((string)$sSendCookies == 'yes') {
            $sCookies = getenv('HTTP_COOKIE');
            if((string)$sCookies != '') {
                $aData['HTTP_COOKIE'] = $this->dibsflex_api_fixCookie($sCookies);
            }
        }
        
        $sDecorator = $this->dibsflex_helper_getconfig('decor');
        if((string)$sDecorator != 'default') {
            $aData['decorator'] = $sDecorator;
        }

        $sColor = $this->dibsflex_helper_getconfig('color');
        if((string)$sColor != 'blank') {
            $aData['color'] = $sColor;
        }

        $sFee = $this->dibsflex_helper_getconfig('fee');
        if((string)$sFee == 'yes') {
            $aData['calcfee'] = 1;
        }
        
        $sTest = $this->dibsflex_helper_getconfig('testmode');
        if((string)$sTest == 'yes') {
            $aData['test'] = 'yes';
        }
            
        $sCapturenow = $this->dibsflex_helper_getconfig('capt');
        if((string)$sCapturenow == 'yes') {
            $aData['capturenow'] = 1;
        }
        
        $sUid = $this->dibsflex_helper_getconfig('uniq');
        if((string)$sUid == 'yes') {
            $aData['uniqueoid'] = 1;
        }
        
        $sVoucher = $this->dibsflex_helper_getconfig('voucher');
        if((string)$sVoucher == 'yes') {
            $aData['voucher'] = 'yes';
        }
    }
    
    /**
     * Gets gateway URL depending to checkout method
     * 
     * @return string 
     */
    function dibsflex_api_getFormAction() {
        return 'https://payment.architrade.com/paymentweb/start.action';
    }
    
    /**
     * Generates cart XML-representation for Klarna, PBB, etc.
     * 
     * @param int $iOrderId
     * @param int $iTotal
     * @param array $aItems
     * @param array $aShippingInfo
     * @return string 
     */
    function dibsflex_api_getInvoiceXML($oOrderInfo) {
        $sVerifiedOrderSum = $oOrderInfo->order->total;
        $Delta1 = 0;
        $Delta2 = 0;
        $Delta3 = 0;
        $Delta4 = 0;

        $doc = new DomDocument("1.0","UTF-8");
        $doc->preserveWhiteSpace = true;
        $doc->formatOutput = true;
 
        $root = $doc->createElement("orderInformation");
        $root = $doc->appendChild($root);
 
        $occ = $doc->createElement("yourRef");
        $occ = $root->appendChild($occ);
        
        $value = $doc->createTextNode($oOrderInfo->order->order_id);
        $value = $occ->appendChild($value);

        $i = 1;
        foreach($oOrderInfo->items as $oItem) {
            if(isset($oItem->price) && !empty($oItem->price) && $oItem->price != 0) {
                $occ = $doc->createElement("orderItem");
                $occ = $root->appendChild($occ);
            
                if(!empty($oItem->name)) {
                    $sTmpName = $this->dibsflex_api_utf8Fix($oItem->name);
                }
                elseif(!empty($oItem->sku)) {
                    $sTmpName = $this->dibsflex_api_utf8Fix($oItem->sku);
                }
                else $sTmpName = $oItem->item_id;
                
                $aAttributs = array('itemID' => $oItem->item_id,
                                    'itemDescription' => $sTmpName,
                                    'comments' => 'SKU: ' . $oItem->sku,
                                    'orderRowNumber' => $i,
                                    'quantity' => $oItem->qty / 1000,
                                    'price' => $oItem->price,
                                    'unitCode' => 'pcs',
                                    'VATAmount' => $oItem->tax_rate);
            
                foreach($aAttributs as $key => $val) {
                    $itemAttr = $doc->createAttribute($key);
                    $occ->appendChild($itemAttr);
                    $itemVal = $doc->createTextNode($val);
                    $itemAttr->appendChild($itemVal);
                }
            
                $Delta1 = ($oItem->qty / 1000) * $oItem->price;
                $sVerifiedOrderSum = $sVerifiedOrderSum - $Delta1;
		 
                $fSingleTax = round($oItem->price * $oItem->tax_rate / 10000);
                $Delta2 = intval(round($fSingleTax * $oItem->qty / 1000));
                $sVerifiedOrderSum =  $sVerifiedOrderSum - $Delta2;
                
                unset($sTmpName, $fSingleTax);
            
                $i++;
            }
        }

        if(isset($oOrderInfo->shipping->rate) && !empty($oOrderInfo->shipping->rate) &&
           $oOrderInfo->shipping->rate != 0) {
        
            $occ = $doc->createElement("orderItem");
            $occ = $root->appendChild($occ);
        
            if(!empty($oOrderInfo->shipping->method)) {
                $sTmpShippingName = $this->dibsflex_api_utf8Fix($oOrderInfo->shipping->method);
            }
            else $sTmpShippingName = "Shipping rate";

            $aAttributs = array('itemID' => 'ShippingCost',
                                'itemDescription' => $sTmpShippingName,
                                'orderRowNumber' => $i,
                                'quantity' => 1,
                                'price' => $oOrderInfo->shipping->rate,
                                'unitCode' => 'pcs',
                                'VATAmount' => $oOrderInfo->shipping->tax);

            foreach($aAttributs as $key => $val) {
                $itemAttr = $doc->createAttribute($key);
                $occ->appendChild($itemAttr);
                $itemVal = $doc->createTextNode($val);
                $itemAttr->appendChild($itemVal);
            }

            unset($sTmpShippingName);
        
            $Delta3 = $oOrderInfo->shipping->rate;
            $sVerifiedOrderSum =  $sVerifiedOrderSum - $Delta3;
        
            $Delta4 = intval(round(($oOrderInfo->shipping->tax / 10000) * $oOrderInfo->shipping->rate));
            $sVerifiedOrderSum =  $sVerifiedOrderSum - $Delta4;
            $i++;
        }

        $sResult = $doc->saveXML();
        return htmlspecialchars($sResult, ENT_COMPAT, "UTF-8");
    }
    
    /**
     * Calculates MD5 for FlexWin API
     * 
     * @param array $aData
     * @return string 
     */
    function dibsflex_api_calcMD5($aData, $bResponse = FALSE) {
        $sMD5key = "";
        $sMD5key1 = trim($this->dibsflex_helper_getconfig('md51'), " ,\t,\r,\n");
        $sMD5key2 = trim($this->dibsflex_helper_getconfig('md52'), " ,\t,\r,\n");
        if ($sMD5key1 != '' && $sMD5key2 != '') {
            if($bResponse === TRUE) {
                if(isset($aData['fee'])) $iAmount = $aData['amount'] + $aData['fee'];
                else $iAmount = $aData['amount'];
                $sMD5key = md5($sMD5key2.md5($sMD5key1 . 
                           'transact=' . $aData['transact'] .
                           '&amount=' . $iAmount . 
                           '&currency=' . $aData['currency']));     
            }
            else {
                $sMD5key = md5($sMD5key2 . md5($sMD5key1 . 
                           'merchant=' . $aData['merchant'] . 
                           '&orderid=' . $aData['orderid'] . 
                           '&currency=' . $aData['currency'] .
                           '&amount=' . $aData['amount']));
            }
        }
        return $sMD5key;
    }
    
    /**
     * Fixes cookie
     * 
     * @param string $sCookie
     * @return string 
     */
    function dibsflex_api_fixCookie($sCookie) {
	if(strpos($sCookie,"%") !== FALSE) $sCookie = urldecode($sCookie);
        $aCookie = explode("; ", $sCookie);
        unset($sCookie);
	for($i=0; $i<count($aCookie); $i++) {
            if(preg_match("/^[^\s;=]+=[^;=]+$/is", $aCookie[$i])) {
                $aNewCookies[] = $aCookie[$i];
            }
        }
        $sNewCookies = implode("; ", $aNewCookies);
        unset($aNewCookies, $aCookie);
        return $sNewCookies;
    }

    /**
     * Returns integer representation of amont. Saves two signs that are
     * after floating point in float number by multiplication by 100.
     * E.g.: converts to cents in money context.
     * Workarround of float to int casting.
     * 
     * @param float $fNum
     * @return int 
     */
    function dibsflex_api_float2intSmartRounding($fNum, $iPrec = 2) {
        return empty($fNum) ? (int)0 : (int)(string)(round($fNum, $iPrec) * pow(10, $iPrec));
    }
    
    /**
     * Fixes UTF-8 special symbols if encoding of CMS is not UTF-8.
     * Main using is for wided latin alphabets.
     * 
     * @param string $sValue
     * @return string 
     */
    function dibsflex_api_utf8Fix($sValue) {
        $sCurEnc = mb_detect_encoding($sValue) ; 
        if($sCurEnc == "UTF-8" && mb_check_encoding($sValue, "UTF-8")) {
            return $sValue;
        }
        else return utf8_encode($sValue);
    }
    
    /**
     * Returns formated paytype parameter
     * 
     * @param string $sPaytype
     * @return string 
     */
    function dibsflex_api_getPaytype($sPaytype) {
        $sNPaytype = "";
        $iTest = $this->dibsflex_helper_getconfig('testmode');
        $selectedpaytypes = explode(',',$sPaytype);
        foreach ($selectedpaytypes as $selectedpaytype){
            if (($iTest == 'yes') && (strtolower($selectedpaytype) == 'pbb')){
                $sNPaytype .= ',pbbtest';
            }
            else $sNPaytype .= ",".$selectedpaytype;
        }
        $sNPaytype = trim($sNPaytype, ",");
        
        return $sNPaytype;
    }

    /**
     * Returns array of currency codes association
     * 
     * @return array 
     */
    function dibsflex_api_getCurrencyArray() {
        $aCurrency = array ('ADP' => '020','AED' => 784,'AFA' => '004','ALL' => '008',
                            'AMD' => '051','ANG' => 532,'AOA' => 973,'ARS' => '032',
                            'AUD' => '036','AWG' => 533,'AZM' => '031','BAM' => 977,
                            'BBD' => '052','BDT' => '050','BGL' => 100,'BGN' => 975,
                            'BHD' => '048','BIF' => 108,'BMD' => '060','BND' => '096',
                            'BOB' => '068','BOV' => 984,'BRL' => 986,'BSD' => '044',
                            'BTN' => '064','BWP' => '072','BYR' => 974,'BZD' => '084',
                            'CAD' => 124,'CDF' => 976,'CHF' => 756,'CLF' => 990,
                            'CLP' => 152,'CNY' => 156,'COP' => 170,'CRC' => 188,
                            'CUP' => 192,'CVE' => 132,'CYP' => 196,'CZK' => 203,
                            'DJF' => 262,'DKK' => 208,'DOP' => 214,'DZD' => '012',
                            'ECS' => 218,'ECV' => 983,'EEK' => 233,'EGP' => 818,
                            'ERN' => 232,'ETB' => 230,'EUR' => 978,'FJD' => 242,
                            'FKP' => 238,'GBP' => 826,'GEL' => 981,'GHC' => 288,
                            'GIP' => 292,'GMD' => 270,'GNF' => 324,'GTQ' => 320,
                            'GWP' => 624,'GYD' => 328,'HKD' => 344,'HNL' => 340,
                            'HRK' => 191,'HTG' => 332,'HUF' => 348,'IDR' => 360,
                            'ILS' => 376,'INR' => 356,'IQD' => 368,'IRR' => 364,
                            'ISK' => 352,'JMD' => 388,'JOD' => 400,'JPY' => 392,
                            'KES' => 404,'KGS' => 417,'KHR' => 116,'KMF' => 174,
                            'KPW' => 408,'KRW' => 410,'KWD' => 414,'KYD' => 136,
                            'KZT' => 398,'LAK' => 418,'LBP' => 422,'LKR' => 144,
                            'LRD' => 430,'LSL' => 426,'LTL' => 440,'LVL' => 428,
                            'LYD' => 434,'MAD' => 504,'MDL' => 498,'MGF' => 450,
                            'MKD' => 807,'MMK' => 104,'MNT' => 496,'MOP' => 446,
                            'MRO' => 478,'MTL' => 470,'MUR' => 480,'MVR' => 462,
                            'MWK' => 454,'MXN' => 484,'MXV' => 979,'MYR' => 458,
                            'MZM' => 508,'NAD' => 516,'NGN' => 566,'NIO' => 558,
                            'NOK' => 578,'NPR' => 524,'NZD' => 554,'OMR' => 512,
                            'PAB' => 590,'PEN' => 604,'PGK' => 598,'PHP' => 608,
                            'PKR' => 586,'PLN' => 985,'PYG' => 600,'QAR' => 634,
                            'ROL' => 642,'RUB' => 643,'RUR' => 810,'RWF' => 646,
                            'SAR' => 682,'SBD' =>'090','SCR' => 690,'SDD' => 736,
                            'SEK' => 752,'SGD' => 702,'SHP' => 654,'SIT' => 705,
                            'SKK' => 703,'SLL' => 694,'SOS' => 706,'SRG' => 740,
                            'STD' => 678,'SVC' => 222,'SYP' => 760,'SZL' => 748,
                            'THB' => 764,'TJS' => 972,'TMM' => 795,'TND' => 788,
                            'TOP' => 776,'TPE' => 626,'TRL' => 792,'TRY' => 949,
                            'TTD' => 780,'TWD' => 901,'TZS' => 834,'UAH' => 980,
                            'UGX' => 800,'USD' => 840,'UYU' => 858,'UZS' => 860,
                            'VEB' => 862,'VND' => 704,'VUV' => 548,'XAF' => 950,
                            'XCD' => 951,'XOF' => 952,'XPF' => 953,'YER' => 886,
                            'YUM' => 891,'ZAR' => 710,'ZMK' => 894,'ZWD' => 716,
        ); 
        return $aCurrency;
    }
  
    /**
     * Returns code from currency array. Use $bFlip === TRUE if opposite ISO code needed.
     * 
     * @param string $sCode
     * @param bool $bFlip
     * @return string 
     */
    function dibsflex_api_getCurrencyValue($sCode, $bFlip = FALSE) {
        $aCurrency = $this->dibsflex_api_getCurrencyArray();
        if($bFlip === TRUE) $aCurrency = array_flip($aCurrency);
        return (string)$aCurrency[$sCode];
    }
    
    /** |---CONTROLLER **/
    
    /**
     * Generates associative array for updating DB data on callback.
     * 
     * @return array 
     */
    function dibsflex_api_DBarray(){
        $aDBFieldsList = array('orderid','amount','currency','test','acquirer',
                         'transact','uniqueoid','paytype','cardnomask','cardcountry',
                         'approvalcode','fee','voucher','amountoriginal','voucheramount',
                         'voucherentry','voucherpaymentid','voucherrest','enrolled',
                         'cardprefix','cardexpdate');

        $aRetFieldsList = $this->dibsflex_api_DBarray_FlexWin();

        return array_combine($aDBFieldsList, $aRetFieldsList);
    }
    
    /**
     * Returned parameters names for FlexWin
     * 
     * @return array 
     */
    function dibsflex_api_DBarray_FlexWin() {
        return array('orderid','amount','currency','test','acquirer','transact',
                     'uniqueoid','paytype','cardnomask','cardcountry','approvalcode',
                     'fee','voucher','amount_original','voucher_amount','voucher_entry',
                     'voucher_payment_id','voucher_rest','enrolled','cardprefix',
                     'cardexpdate');
    }
     
    /**
     * Checks required fields returned from gateway on success and callback.
     * 
     * @param type $oOrder
     * @return type 
     */
    function dibsflex_api_checkMainFields($oOrder) {
        
        if (isset($_POST['orderid'])) {
            $oOrder = $this->dibsflex_helper_getOrderObj($oOrder, TRUE);
            if(!$oOrder->order_id) return 11;
        }
        else return 12;

        if (isset($_POST['voucher_amount']) && $_POST['voucher_amount'] > 0) {
            if(isset($_POST['amount']) && $_POST['amount'] > 0) {
                $iAmount = $_POST['amount_original'];
            }
            else $iAmount = $_POST['voucher_amount'];
        }
        else $iAmount = $_POST['amount'];

        if (isset($_POST['fee'])) {
            $iFeeAmount = $iAmount - $_POST['fee'];
        }
        
        if (isset($_POST['amount'])) {
		if ((abs((int)$iAmount - $oOrder->total) >= 0.01) && 
                   (abs((int)$iFeeAmount - $oOrder->total) >= 0.01)) return 21;
	}
        else return 22;

	if (isset($_POST['currency'])) {
            if ((int)$oOrder->currency != (int)$_POST['currency']) return 31;
        }
        else return 32;
                
        if ($this->dibsflex_helper_getconfig('md51') != "" && 
            $this->dibsflex_helper_getconfig('md52') != "") {
            if ($this->dibsflex_api_checkMD5($_POST) !== TRUE) return 41;
        }

        return FALSE;
    }
    
    /**
     * Compare calculated MD5 with MD5 from response
     * 
     * @param array $aReq
     * @return bool 
     */
    function dibsflex_api_checkMD5($aReq) {
        $sReqMD5 = $aReq['authkey'];
        unset($aReq['authkey']);
        $sMD5 = $this->dibsflex_api_calcMD5($aReq, TRUE);
        if($sReqMD5 == $sMD5) return TRUE;
        else return FALSE;
    }
    
    /**
     * Collects invoice response information.
     * 
     * @param array $aFields 
     */
    function dibsflex_api_callbackPBB(&$aFields) {
        $aFields['PBB_customerId'] = isset($_POST['customerId']) ? $_POST['customerId'] : "-";
	$aFields['PBB_deliveryAddress'] = isset($_POST['deliveryAddress']) ? iconv("ISO-8859-1","UTF-8",$_POST['deliveryAddress']) : "-";		
        $aFields['PBB_deliveryCountryCode'] = isset($_POST['deliveryCountryCode']) ? iconv("ISO-8859-1","UTF-8",$_POST['deliveryCountryCode']) : "-";
        $aFields['PBB_deliveryPostalCode'] = isset($_POST['deliveryPostalCode']) ? iconv("ISO-8859-1","UTF-8",$_POST['deliveryPostalCode']) : "-";
        $aFields['PBB_deliveryPostalPlace'] = isset($_POST['deliveryPostalPlace']) ? iconv("ISO-8859-1","UTF-8",$_POST['deliveryPostalPlace']) : "-";
        $aFields['PBB_firstName'] = isset($_POST['firstName']) ? iconv("ISO-8859-1","UTF-8",$_POST['firstName']) : "-";
        $aFields['PBB_lastName'] = isset($_POST['lastName']) ? iconv("ISO-8859-1","UTF-8",$_POST['lastName']) : "";
    }
    
    /**
     * Generates error messages if check failed.
     * 
     * @param int $iErrCode
     * @return string 
     */
    function dibsflex_api_errCodeToMessage($iErrCode) {
        $sToShopLink = $this->dibsflex_helper_getReturnURLs('cart');
        $sErrBegin = "<h1>" . $this->dibsflex_helper_getlang('txt_err_fatal') . "</h1>";
        $sErrEnd =   "<br><br> <button type=\"button\" onclick=window.location.replace('" . 
                     $sToShopLink . "')>" . $this->dibsflex_helper_getlang('txt_msg_toshop') . 
                     "</button>";
        
        $sErrMessage = $this->dibsflex_helper_getlang('txt_err_' . $iErrCode);
        if($sErrMessage == "") {
            $sErrMessage = $this->dibsflex_helper_getlang('txt_err_def');
        }
        
        return $sErrBegin . $sErrMessage . $sErrEnd;
    }
    
    /**
     * Calls helpers to create unified order object.
     * 
     * @param mixed $mOrderInfo
     * @return object 
     */
    function dibsflex_api_orderObject($mOrderInfo) {
        return (object)array(
            'order'    => $this->dibsflex_helper_getOrderObj($mOrderInfo),
            'items'    => $this->dibsflex_helper_getItemsObj($mOrderInfo),
            'shipping' => $this->dibsflex_helper_getShippingObj($mOrderInfo),
            'customer' => $this->dibsflex_helper_getAddressObj($mOrderInfo)
        );
    }
    
    /**
     * Encodes string for safe sql transaction.
     * 
     * @param string $sValue
     * @return string 
     */
    function dibsflex_api_sqlEncode($sValue) {
        return addslashes(str_replace("`","'",$sValue));
    }

    /**
     * Performs "oncallback" operations.
     * 
     * @param object $oOrder 
     */
    function dibsflex_api_callback($oOrder) {
        $mErr = $this->dibsflex_api_checkMainFields($oOrder);
        if($mErr !== FALSE) exit((string)$mErr);
        
        $mStatus = $this->dibsflex_helper_dbquery_read("SELECT `status` FROM `" . 
                                             $this->dibsflex_helper_getdbprefix() . 
                                            "dibs_orderdata` WHERE `orderid` = '" . 
                                             $this->dibsflex_api_sqlEncode($_POST['orderid']) . 
                                            "' LIMIT 1;");
        if ($this->dibsflex_helper_dbquery_read_single($mStatus, 'status') == 0) {
            
            $aFieldsList = $this->dibsflex_api_DBarray();
            $aFields = array();
            foreach($aFieldsList as $key => $val) {
                if(isset($_POST[$val])) {
                    $aFields[$key] = $_POST[$val];
                }
                else $_POST[$key] = 0;
            }
            
            $this->dibsflex_api_callbackPBB($aFields);
            
            $aFields['callback'] = '1';
            $aFields['status'] = '1';
            
            $sUpdate = '';
            foreach ($aFields as $sCell => $sValue) {
                $sUpdate .= '`' . $sCell.'`=' . "'" . $this->dibsflex_api_sqlEncode($sValue) . "',";
            }
            $sUpdate = rtrim($sUpdate, ",");
            $this->dibsflex_helper_dbquery_write("UPDATE `" . 
                                       $this->dibsflex_helper_getdbprefix() . 
                                      "dibs_orderdata` SET " . $sUpdate . 
                                      " WHERE `orderid`=" .
                                      $aFields['orderid']." LIMIT 1;");
            
            if(method_exists($this, 'dibsflex_helper_callbackHook') && 
               is_callable(array($this, 'dibsflex_helper_callbackHook'))) {
                $this->dibsflex_helper_callbackHook($oOrder);
            }
        }
        else exit();
    }
    
    /** START OF CGI API **/
    
    /**
     * Returns form with CGI API controls.
     * 
     * @param string $sOrderId
     * @return string 
     */
    function dibsflex_api_cgibuttons($sOrderId) {
        $sOutput = "";
        $sApiLogin = $this->dibsflex_helper_getconfig("apiuser");
        $sApiPass = $this->dibsflex_helper_getconfig("apipass");
        if(!empty($sApiLogin) && !empty($sApiPass)) {
            $sTransac = $this->dibsflex_api_getDibsOrder($sOrderId);
            if($sTransac != "") {
                $sState = $this->dibsflex_api_payinfo($sTransac, $sApiLogin, 
                                                                 $sApiPass);
            }
            else $sState = "Empty transaction ID.";
            
            if (strpos($sState, "&") !== FALSE && strpos($sState, "=") !== FALSE) {
                $oState = $this->dibsflex_api_getAsObj($sState);
                if(isset($oState->status)) {
                    $mStatus = $this->dibsflex_api_getStatusById($oState->status);
                }
                
                $sOutput = '<!-- bof_dibsbuttons (please, do not change this comment) -->' .
                           '<table style="font-size: inherit; vertical-align: inherit;' .
                           'color: inherit; border-collapse: collapse; border: none; padding: 0px;'. 
                           'margin: 0px;">';
                if($mStatus !== FALSE) {
                    $sOutput .= '<tr><td style="padding: 5px 5px 5px 0px;">Status:</td><td style="padding: 5px;">' . 
                                $mStatus . '</td></tr>';
                }

                switch ($oState->status) {
                    case 2:
                        $sActions = '<input class="'.$this->dibsflex_helper_cgiButtonsClass() . 
                                    '" type="submit" name="cgicancel" value="Cancel" />&nbsp;' .
                                    '<input class="'.$this->dibsflex_helper_cgiButtonsClass() . 
                                    '" type="submit" name="cgicapture" value="Capture" />';
                    break;
                    case 5:
                        $sActions = '<input class="'.$this->dibsflex_helper_cgiButtonsClass() . 
                                    '" type="submit" name="cgirefund" value="Refund" />';
                    break;
                }
                
                if($oState->status == 2 || $oState->status == 5) {
                    $sOutput .= '<tr><td style="vertical-align: top; padding: 5px 5px 5px 0px;">' .
                                'Actions:</td><td style="padding: 5px;">' .
                                '<form id="dibsflex_cgiform" action="' .  
                                $this->dibsflex_helper_getReturnURLs('cgi') . 
                                '" method="post"><div>' .
                                '<input type="hidden" name="transact" value="' . $sTransac . '" />' .
                                '<input type="hidden" name="currency" value="' . $oState->currency . '" />' .
                                '<input type="hidden" name="amount" value="' . $oState->amount . '" />' .
                                '<input type="hidden" name="orderid" value="' . $oState->orderid . '" />' .
                                '<input type="hidden" name="dibsflexreturn" value="' .
                                $this->dibsflex_api_getFullURL() . '" />' .
                                $sActions . '</div></form></td></tr>';
                }
                
                $sOutput .= '</table><!-- eof_dibsbuttons (please, do not change this comment)-->';
            }
            elseif (strlen(trim($sState, " ,\n,\r,\t")) <= 100) {
                $sOutput = "Error: " . $sState;
            }
            elseif (strpos($sState, "Login problems?") !== FALSE) {
                $sOutput = "Error: Invalid API credentials. Set correct and try again in 30 minutes.";
            }
        }
        else $sOutput = "Error: Empty API credentials. Set correct and try again in 30 minutes.";
        
        return $sOutput;
    }

    /**
     * Generate URL to redirect user back after API query performed.
     * 
     * @return string 
     */
    function dibsflex_api_getFullURL() {
        $sPageURL = 'http';

        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $sPageURL .= "s";
        }
        
        $sPageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $sPageURL .= $_SERVER["SERVER_NAME"] . ":" . 
                         $_SERVER["SERVER_PORT"] . 
                         $_SERVER["REQUEST_URI"];
        }
        else {
            $sPageURL .= $_SERVER["SERVER_NAME"] . 
                         $_SERVER["REQUEST_URI"];
        }
        
        return $sPageURL;
    }

    /**
     * Performs requested API query and redirects user back.
     */
    function dibsflex_api_cgiapi() {
        $sApiLogin = $this->dibsflex_helper_getconfig("apiuser");
        $sApiPass = $this->dibsflex_helper_getconfig("apipass");

        if(!empty($sApiLogin) && !empty($sApiPass)) {
            if(isset($_POST['cgicancel'])) {
                $sAPI = 'cancel';
                $sApiLink = 'https://' . $sApiLogin . ':' . $sApiPass . 
                            '@payment.architrade.com/cgi-adm/cancel.cgi';
            }
            elseif(isset($_POST['cgicapture'])){
                $sAPI = 'capture';
                $sApiLink = 'https://payment.architrade.com/cgi-bin/capture.cgi';
            }
            elseif(isset($_POST['cgirefund'])) {
                $sAPI = 'refund';
                $sApiLink = 'https://' . $sApiLogin . ':' . $sApiPass . 
                            '@payment.architrade.com/cgi-adm/refund.cgi';
            }
            else $this->dibsflex_helper_redirect($_POST['dibsflexreturn']);
    
            $aParams = array(
                'orderid'   => $_POST['orderid'],
                'merchant'  => $this->dibsflex_helper_getconfig("mid"),
                'transact'  => $_POST['transact'],
                'textreply' => 'yes',
                'currency'  => $_POST['currency'],
                'amount'    => $_POST['amount']
            );
            
            $sAccount = $this->dibsflex_helper_getconfig("account");
            if(!empty($sAccount)) $aParams['account'] = $sAccount;
    
            $sMD5 = $this->dibsflex_api_cgiCalcMD5($aParams, $sAPI);
            if($sMD5 != "") $aParams['md5key'] = $sMD5;

            $sRes = $this->dibsflex_api_postcgi($sApiLink, $aParams);
            $this->dibsflex_helper_redirect($_POST['dibsflexreturn']);
        }
        else $this->dibsflex_helper_redirect($_POST['dibsflexreturn']);
    }

    /**
     * Generate object from server status response.
     * 
     * @param string $sGet
     * @return object 
     */
    function dibsflex_api_getAsObj($sGet) {
        $aResult = array();
        $aGet = explode("&", $sGet);
        for($i=0; $i<count($aGet); $i++) {
            $aTmp = explode("=", $aGet[$i]);
            if(isset($aTmp[0]) && isset($aTmp[1])) {
                $aResult[$aTmp[0]] = $aTmp[1];
            }
        
            unset($aTmp);
        }
        return (object)$aResult;
    }

    /**
     * Performs API request to detect current status of transaction.
     * 
     * @param string $sTransac
     * @param string $sApiLogin
     * @param string $sApiPass
     * @return string 
     */
    function dibsflex_api_payinfo($sTransac, $sApiLogin, $sApiPass) {
        $sResult = $this->dibsflex_api_postcgi("https://" . $sApiLogin . ":" . 
                   $sApiPass . "@payment.architrade.com/cgi-adm/payinfo.cgi", 
                   array('transact' => $sTransac));
        return $sResult;
    }

    /**
     * Calculate MD5 checksum for each type of API requests.
     * 
     * @param array $aData
     * @param string $sAPI
     * @return string 
     */
    function dibsflex_api_cgiCalcMD5($aData, $sAPI) {
        $sMD5key = "";
        $sMD5key1 = trim($this->dibsflex_helper_getconfig('md51'), " ,\t,\r,\n");
        $sMD5key2 = trim($this->dibsflex_helper_getconfig('md52'), " ,\t,\r,\n");
        if ($sMD5key1 != '' && $sMD5key2 != '') {
            switch ($sAPI) {
                case 'cancel':
                    $sMD5key = md5($sMD5key2.md5($sMD5key1 . 
                                   'merchant=' . $aData['merchant'] .
                                   '&orderid=' . $aData['orderid'] .
                                   '&transact=' . $aData['transact']));
                break;
                case 'capture':
                case 'refund':
                    $sMD5key = md5($sMD5key2.md5($sMD5key1 . 
                                   'merchant=' . $aData['merchant'] .
                                   '&orderid=' . $aData['orderid'] .
                                   '&transact=' . $aData['transact'] .
                                   '&amount=' . $aData['amount']));
                break;
            }
        }
    
        return $sMD5key;
    }
    
    
    /**
     * Process all API queries with cURL lib.
     * 
     * @param string $sURL
     * @param array $aParams
     * @return string 
     */
    function dibsflex_api_postcgi($sURL, $aParams) {
        $sResult = "";
        $ch = curl_init($sURL);
        if($ch) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $aParams);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            $sResult = curl_exec($ch);
            curl_close($ch);
        }
        
        return $sResult;
    }
    
    /**
     * Returns transaction number from DB by order ID.
     * 
     * @param string $sOrderId
     * @return string
     */
    function dibsflex_api_getDibsOrder($sOrderId) {
        $mResult = $this->dibsflex_helper_dbquery_read("SELECT `transact` AS transact FROM " . 
                                     $this->dibsflex_helper_getdbprefix() . "dibs_orderdata 
                                     WHERE orderid=".$sOrderId." AND `status`=1 LIMIT 1");
    
        $sRes = $this->dibsflex_helper_dbquery_read_single($mResult, 'transact');
        return $sRes;
    }

    /**
     * Returns title of status by its ID or FALSE if not found.
     * 
     * @param string $sId
     * @return mixed 
     */
    function dibsflex_api_getStatusById($sId) {
        $aStates = array(
            '0'   => 'Authorization shipped',
            '1'   => 'Authorization denied',
            '2'   => 'New (auth approved)',
            '3'   => 'Capture shipped',
            '4'   => 'Capture rejected',
            '5'   => 'Captured',
            '6'   => 'Canceled',
            '9'   => 'Refund shipped',
            '10'  => 'Refund rejected',
            '11'  => 'Refund approved'
        );
    
        if(isset($aStates[$sId])) return $aStates[$sId];
        else return FALSE;
    }
    
    /** EOF CGI API **/
}
?>