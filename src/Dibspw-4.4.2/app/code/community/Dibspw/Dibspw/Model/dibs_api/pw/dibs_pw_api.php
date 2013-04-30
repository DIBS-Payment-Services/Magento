<?php
require_once str_replace('\\', '/', dirname(__FILE__)) . '/dibs_pw_helpers_interface.php';
require_once str_replace('\\', '/', dirname(__FILE__)) . '/dibs_pw_helpers_cms.php';
require_once str_replace('\\', '/', dirname(__FILE__)) . '/dibs_pw_helpers.php';

class dibs_pw_api extends dibs_pw_helpers {

    /**
     * DIBS responses log table.
     * 
     * @var string
     */
    private static $sDibsTable   = 'dibs_pw_results';

    /**
     * Settings of module inner template engine.
     * 
     * @var array 
     */
    private static $aTemplates   = array('folder' => 'tmpl',
                                         'marker' => '#',
                                         'autotranslate' => array('lbl','msg', 'sts', 'err'),
                                         'tmpls' => array('error' => 'dibs_pw_error'));

    /**
     * Default currency code (in two ISO formats).
     * 
     * @var array 
     */
    private static $sDefaultCurr = array(0 => 'EUR', 1 => '978');

    /**
     * DIBS gateway URL.
     * 
     * @var string 
     */
    private static $sFormAction  = 'https://sat1.dibspayment.com/dibspaymentwindow/entrypoint';
    
    /**
     * Dictionary of DIBS response to self::$sDibsTable table fields relations.
     * 
     * @var array 
     */
    private static $aRespFields  = array('orderid' => 'orderid', 'status' => 'status',
                                         'testmode' => 'test', 'transaction' => 'transaction', 
                                         'amount' => 'amount', 'currency' => 'currency',
                                         'fee' => 'fee', 'voucheramount' => 'voucherAmount',
                                         'paytype' => 'cardTypeName', 'actioncode' => 'actionCode',
                                         'amountoriginal'=>'amountOriginal', 'sysmod' => 's_sysmod',
                                         'validationerrors'=>'validationErrors',
                                         'capturestatus' => 'captureStatus');
    
    /**
     * Array of currency's with of digits after the decimal separator.
     * 
     * @var array 
     */
    
 private static $aCurrency = array(
                'AFA'=>array('004',2),'ALL'=>array('008',2),'AMD'=>array('051',2),'ANG'=>array('532',2),
                'AOA'=>array('973',2),'ARS'=>array('032',2),'AUD'=>array('036',2),'AWG'=>array('533',2),
                'BAM'=>array('977',2),'BBD'=>array('052',2),'BDT'=>array('050',2),'BGN'=>array('975',2),
                'BHD'=>array('048',3),'BIF'=>array('108',0),'BMD'=>array('060',2),'BND'=>array('096',2),
                'BOB'=>array('068',2),'BRL'=>array('986',2),'BSD'=>array('044',2),'BTN'=>array('064',2),
                'BWP'=>array('072',2),'BYR'=>array('974',0),'BZD'=>array('084',2),'CAD'=>array('124',2),
                'CDF'=>array('976',2),'CHF'=>array('756',2),'CLP'=>array('152',0),'CNY'=>array('156',2),
                'COP'=>array('170',2),'CRC'=>array('188',2),'CUP'=>array('192',2),'CVE'=>array('132',2),
                'CZK'=>array('203',2),'DJF'=>array('262',0),'DKK'=>array('208',2),'DOP'=>array('214',2),
                'DZD'=>array('012',2),'EGP'=>array('818',2),'ERN'=>array('232',2),'ETB'=>array('230',2),
                'EUR'=>array('978',2),'FJD'=>array('242',2),'FKP'=>array('238',2),'GBP'=>array('826',2),
                'GEL'=>array('981',2),'GIP'=>array('292',2),'GMD'=>array('270',2),'GNF'=>array('324',0),
                'GTQ'=>array('320',2),'GYD'=>array('328',2),'HKD'=>array('344',2),'HNL'=>array('340',2),
                'HRK'=>array('191',2),'HTG'=>array('332',2),'HUF'=>array('348',2),'IDR'=>array('360',2),
                'ILS'=>array('376',2),'INR'=>array('356',2),'IQD'=>array('368',3),'IRR'=>array('364',2),
                'ISK'=>array('352',0),'JMD'=>array('388',2),'JOD'=>array('400',3),'JPY'=>array('392',0),
                'KES'=>array('404',2),'KGS'=>array('417',2),'KHR'=>array('116',2),'KMF'=>array('174',0),
                'KPW'=>array('408',2),'KRW'=>array('410',0),'KWD'=>array('414',3),'KYD'=>array('136',2),
                'KZT'=>array('398',2),'LAK'=>array('418',2),'LBP'=>array('422',2),'LKR'=>array('144',2),
                'LRD'=>array('430',2),'LSL'=>array('426',2),'LTL'=>array('440',2),'LVL'=>array('428',2),
                'LYD'=>array('434',3),'MAD'=>array('504',2),'MDL'=>array('498',2),'MKD'=>array('807',2),
                'MMK'=>array('104',2),'MNT'=>array('496',2),'MOP'=>array('446',2),'MRO'=>array('478',0),
                'MUR'=>array('480',2),'MVR'=>array('462',2),'MWK'=>array('454',2),'MXN'=>array('484',2),
                'MYR'=>array('458',2),'NAD'=>array('516',2),'NGN'=>array('566',2),'NIO'=>array('558',2),
                'NOK'=>array('578',2),'NPR'=>array('524',2),'NZD'=>array('554',2),'OMR'=>array('512',3),
                'PAB'=>array('590',2),'PEN'=>array('604',2),'PGK'=>array('598',2),'PHP'=>array('608',2),
                'PKR'=>array('586',2),'PLN'=>array('985',2),'PYG'=>array('600',0),'QAR'=>array('634',2),
                'RUB'=>array('643',2),'RWF'=>array('646',0),'SAR'=>array('682',2),'SBD'=>array('090',2),
                'SCR'=>array('690',2),'SEK'=>array('752',2),'SGD'=>array('702',2),'SHP'=>array('654',2),
                'SLL'=>array('694',2),'SOS'=>array('706',2),'STD'=>array('678',2),'SVC'=>array('222',2),
                'SYP'=>array('760',2),'SZL'=>array('748',2),'THB'=>array('764',2),'TJS'=>array('972',2),
                'TND'=>array('788',3),'TOP'=>array('776',2),'TRY'=>array('949',2),'TTD'=>array('780',2),
                'TWD'=>array('901',2),'TZS'=>array('834',2),'UAH'=>array('980',2),'UGX'=>array('800',2),
                'USD'=>array('840',2),'UYU'=>array('858',2),'UZS'=>array('860',2),'VND'=>array('704',0),
                'VUV'=>array('548',0),'XAF'=>array('950',0),'XCD'=>array('951',2),'XOF'=>array('952',0),
                'XPF'=>array('953',0),'YER'=>array('886',2),'ZAR'=>array('710',2),'ZMK'=>array('894',2),
                'ADP'=>array('020',0),'AZM'=>array('031',0),'BGL'=>array('100',2),'BOV'=>array('984',2),
                'CLF'=>array('990',0),'CYP'=>array('196',2),'ECS'=>array('218',0),'ECV'=>array('983',0),
                'EEK'=>array('233',2),'GHC'=>array('288',0),'GWP'=>array('624',2),'MGF'=>array('450',0),
                'MTL'=>array('470',2),'MXV'=>array('979',2),'MZM'=>array('508',0),'ROL'=>array('642',2),
                'RUR'=>array('810',2),'SDD'=>array('736',0),'SIT'=>array('705',1),'SKK'=>array('703',1),
                'SRG'=>array('740',2),'TMM'=>array('795',0),'TPE'=>array('626',2),'TRL'=>array('792',0),
                'VEB'=>array('862',2),'YUM'=>array('891',2),'ZWD'=>array('716',2));

 
    
    /**
     * Returns CMS order common information converted to standardized order information objects.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    private function api_dibs_commonOrderObject($mOrderInfo) {
        return (object)array(
            'order' => $this->helper_dibs_obj_order($mOrderInfo),
            'urls'  => $this->helper_dibs_obj_urls($mOrderInfo),
            'etc'   => $this->helper_dibs_obj_etc($mOrderInfo)
        );
    }

    /**
     * Returns CMS order invoice information converted to standardized order information objects.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return object 
     */
    private function api_dibs_invoiceOrderObject($mOrderInfo) {
        return (object)array(
            'items' => $this->helper_dibs_obj_items($mOrderInfo),
            'ship'  => $this->helper_dibs_obj_ship($mOrderInfo),
            'addr'  => $this->helper_dibs_obj_addr($mOrderInfo)
        );
    }

    /**
     * Collects API parameters to send in dependence of checkout type. API entry point.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return array 
     */
    final public function api_dibs_get_requestFields($mOrderInfo) {
        $aData = array();
        $oOrder = $this->api_dibs_commonOrderObject($mOrderInfo);
        $this->api_dibs_prepareDB($oOrder->order->orderid);
        $this->api_dibs_commonFields($aData, $oOrder);
        $this->api_dibs_invoiceFields($aData, $mOrderInfo);
        if(count($oOrder->etc) > 0) {
            foreach($oOrder->etc as $sKey => $sVal) $aData['s_' . $sKey] = $sVal;
        }
        $sMAC = $this->api_dibs_calcMAC($aData, $this->helper_dibs_tools_conf('HMAC'));
        if(!empty($sMAC)) $aData['MAC'] = $sMAC;
        
        return $aData;
    }
    
    /**
     * Adds to $aData common DIBS integration parameters.
     * 
     * @param array $aData Array to fill in by link with DIBS API parameters.
     * @param object $oOrder Formated to object order common information.
     */
    private function api_dibs_commonFields(&$aData, $oOrder) {
        self::$sDefaultCurr = $oOrder->order->currency;
        $aData['orderid']   = $oOrder->order->orderid;
        $aData['merchant']  = $this->helper_dibs_tools_conf('mid');
        $aData['amount']   = self::api_dibs_round($oOrder->order->amount, dibs_pw_api::api_dibs_get_currencyMinValue( $oOrder->order->currency ));
        $aData['currency'] = $oOrder->order->currency;
        $aData['language'] = $this->helper_dibs_tools_conf('lang');
        if((string)$this->helper_dibs_tools_conf('fee') == 'yes') $aData['addfee'] = 1;
        if((string)$this->helper_dibs_tools_conf('testmode') == 'yes') $aData['test'] = 1;
        $sPaytype = $this->helper_dibs_tools_conf('paytype');
        if(!empty($sPaytype)) $aData['paytype'] = $sPaytype;
        $sAccount = $this->helper_dibs_tools_conf('account');
        if(!empty($sAccount)) $aData['account'] = $sAccount;
        $aData['acceptreturnurl'] = $this->helper_dibs_tools_url($oOrder->urls->acceptreturnurl);
        $aData['cancelreturnurl'] = $this->helper_dibs_tools_url($oOrder->urls->cancelreturnurl);
        $aData['callbackurl']     = $oOrder->urls->callbackurl;
        if(strpos($aData['callbackurl'], '/5c65f1600b8_dcbf.php') === FALSE) {
            $aData['callbackurl'] = $this->helper_dibs_tools_url($aData['callbackurl']);
        }
    }
    
    /**
     * Adds Invoice API parameters specific for SAT PW.
     * 
     * @param array $aData  Array to fill in by link with DIBS API parameters.
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     */
    private function api_dibs_invoiceFields(&$aData, $mOrderInfo) {
        $oOrder = $this->api_dibs_invoiceOrderObject($mOrderInfo);
        foreach($oOrder->addr as $sKey => $sVal) {
            $aData[$sKey] = $sVal;
        }
        
        $oOrder->items[] = $oOrder->ship;
        if(isset($oOrder->items) && count($oOrder->items) > 0) {
            $aData['oitypes'] = 'QUANTITY;UNITCODE;DESCRIPTION;AMOUNT;ITEMID';
            $aData['oinames'] = 'Qty;UnitCode;Description;Amount;ItemId';
            if(isset($oOrder->items[0]->tax)) {
                $aData['oitypes'] .= (self::$bTaxAmount ? ';VATAMOUNT' : ';VATPERCENT');
                $aData['oinames'] .= (self::$bTaxAmount ? ';VatAmount' : ';VatPercent');
            }
            $i = 1;
            foreach($oOrder->items as $oItem) {
                $iTmpPrice = self::api_dibs_round($oItem->price, dibs_pw_api::api_dibs_get_currencyMinValue( self::$sDefaultCurr ));
                if(!empty($iTmpPrice)) {
                    $sTmpName = !empty($oItem->name) ? $oItem->name : $oItem->sku;
                    if(empty($sTmpName)) $sTmpName = $oItem->id;

                    $aData['oiRow' . $i++] = 
                        self::api_dibs_round($oItem->qty, 3) / 1000 . ';' . 
                        'pcs;' . 
                        self::api_dibs_utf8Fix(str_replace(';','\;',$sTmpName)) . ';' .
                        $iTmpPrice . ';' .
                        self::api_dibs_utf8Fix(str_replace(';','\;',$oItem->id)) . 
                        (isset($oItem->tax) ? ';' . self::api_dibs_round($oItem->tax) : '');
                }
                unset($iTmpPrice);
            }
	}
        if(!empty($aData['orderid'])) $aData['yourRef'] = $aData['orderid'];
        if((string)$this->helper_dibs_tools_conf('capturenow') == 'yes') $aData['capturenow'] = 1;
        $sDistribType = $this->helper_dibs_tools_conf('distr');
        if((string)$sDistribType != 'empty') $aData['distributiontype'] = strtoupper($sDistribType);
    }
    
    /**
     * Process DB preparations and adds empty transaction record before payment.
     * 
     * @param int $iOrderId Order ID to insert to self::$sDibsTable table in DB.
     */
    private function api_dibs_prepareDB($iOrderId) {
        $this->api_dibs_checkTable();
        $sQuery = "SELECT COUNT(`orderid`) AS order_exists 
                   FROM `" . $this->helper_dibs_tools_prefix() . self::api_dibs_get_tableName() . "` 
                   WHERE `orderid` = '" . self::api_dibs_sqlEncode($iOrderId) . "' LIMIT 1;";
        if($this->helper_dibs_db_read_single($sQuery, 'order_exists') <= 0) {
            $this->helper_dibs_db_write("INSERT INTO `" . $this->helper_dibs_tools_prefix() . 
                                        self::api_dibs_get_tableName() . "`(`orderid`) 
                                        VALUES('" . $iOrderId."')");
        }
    }
    
    /**
     * Creates dibs_results DB if not exists.
     */
    public final function api_dibs_checkTable() {
        $this->helper_dibs_db_write(
            "CREATE TABLE IF NOT EXISTS `" . $this->helper_dibs_tools_prefix() . 
                self::api_dibs_get_tableName() . "` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `orderid` varchar(100) NOT NULL DEFAULT '',
                `status` varchar(10) NOT NULL DEFAULT '',
                `testmode` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `transaction` varchar(100) NOT NULL DEFAULT '',
                `amount` int(10) unsigned NOT NULL DEFAULT '0',
                `currency` varchar(3) NOT NULL DEFAULT '',
                `fee` int(10) unsigned NOT NULL DEFAULT '0',
                `paytype` varchar(32) NOT NULL DEFAULT '',
                `voucheramount` int(10) unsigned NOT NULL DEFAULT '0',
                `amountoriginal` int(10) unsigned NOT NULL DEFAULT '0',
                `ext_info` text,
                `validationerrors` text,
                `capturestatus` varchar(10) NOT NULL DEFAULT '0',
                `actioncode` varchar(20) NOT NULL DEFAULT '',
                `success_action` tinyint(1) unsigned NOT NULL DEFAULT '0' 
                    COMMENT '0 = NotPerformed, 1 = Performed',
                `cancel_action` tinyint(1) unsigned NOT NULL DEFAULT '0' 
                    COMMENT '0 = NotPerformed, 1 = Performed',
                `callback_action` tinyint(1) unsigned NOT NULL DEFAULT '0' 
                    COMMENT '0 = NotPerformed, 1 = Performed',
                `success_error` varchar(100) NOT NULL DEFAULT '',
                `callback_error` varchar(100) NOT NULL DEFAULT '',
                `sysmod` varchar(10) NOT NULL DEFAULT '',
                PRIMARY KEY (`id`),
                KEY `orderid` (`orderid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );
    }

    /**
     * Checks existance and integrity of main fields, that returned to shop after payment.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @param bool $bUrlDecode Flag if urldecode before MAC hash calculation is needed (for success action).
     * @return int 
     */
    final public function api_dibs_checkMainFields($mOrderInfo, $bUrlDecode = TRUE) {
        if(!isset($_POST['orderid']) || empty($_POST['orderid'])) return 1;
        elseif(!isset($_POST['amount'])) return 3;
        elseif(!isset($_POST['currency'])) return 5;
        
        $mOrderInfo = $this->helper_dibs_obj_order($mOrderInfo, TRUE);
        if(!isset($mOrderInfo->orderid) || empty($mOrderInfo->orderid)) return 2;
        
        $iAmount = (isset($_POST['voucherAmount']) && $_POST['voucherAmount'] > 0) ? 
                    $_POST['amountOriginal'] : $_POST['amount'];
        if(abs((int)$iAmount - (int)self::api_dibs_round($mOrderInfo->amount, dibs_pw_api::api_dibs_get_currencyMinValue( $_POST['currency'] ) )) > 1) return 4;
 
        if((int)$mOrderInfo->currency != (int)$_POST['currency']) return 6;
          
        $sHMAC = $this->helper_dibs_tools_conf('hmac');
        if(!empty($sHMAC) && self::api_dibs_checkMAC($sHMAC, $bUrlDecode) !== TRUE) return 7;
        
        return 0;
    }

    /**
     * Give fallback verification error page 
     * if module has no ability to use CMS for error displaying.
     * 
     * @param int $iCode Error code.
     * @return string 
     */
    public function api_dibs_getFatalErrorPage($iCode) {
        return $this->api_dibs_renderTemplate(self::$aTemplates['tmpls']['error'],
                   array('errname_err' => 0,
                         'errcode_msg' => 'errcode',
                         'errcode'     => $iCode,
                         'errmsg_msg'  => 'errmsg',
                         'errmsg_err'  => $iCode,
                         'link_toshop' => $this->helper_dibs_obj_urls()->carturl,
                         'toshop_msg'  => 'toshop'));
    }
    
    /**
     * Processes success redirect from payment gateway.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     * @return int 
     */
    final public function api_dibs_action_success($mOrderInfo) {
        $iErr = $this->api_dibs_checkMainFields($mOrderInfo);
        if($iErr != 1 && $iErr != 2) {
            $this->api_dibs_updateResultRow(array('success_action' => empty($iErr) ? 1 : 0,
                                                  'success_error' => $iErr));
        }
        
        return (int)$iErr;
    }
    
    /**
     * Processes cancel from payment gateway.
     */
    final public function api_dibs_action_cancel() {
        if(isset($_POST['orderid']) && !empty($_POST['orderid'])) {
            $this->api_dibs_updateResultRow(array('cancel_action' => 1));
        }
    }
    
    /**
     * Processes callback from payment gateway.
     * 
     * @param mixed $mOrderInfo All order information, needed for DIBS (in shop format).
     */
    final public function api_dibs_action_callback($mOrderInfo) {
        $iErr = $this->api_dibs_checkMainFields($mOrderInfo, FALSE);
        if(!empty($iErr)) {
            if($iErr != 1 && $iErr != 2) {
                $this->api_dibs_updateResultRow(array('callback_error' => $iErr));
            }
            exit((string)$iErr);
        }
        
   	$sResult = $this->helper_dibs_db_read_single("SELECT `status` FROM `" . 
                   $this->helper_dibs_tools_prefix() . self::api_dibs_get_tableName() . 
                   "` WHERE `orderid` = '" . self::api_dibs_sqlEncode($_POST['orderid']) . 
                   "'  LIMIT 1;", 'status');
        if(empty($sResult)) {
            $aFields = array('callback_action' => 1);
            $aResponse = $_POST;
            foreach(self::$aRespFields as $sDbKey => $sPostKey) {
                if(!empty($sPostKey) && isset($_POST[$sPostKey])) {
                    unset($aResponse[$sPostKey]);
                    $aFields[$sDbKey] = $_POST[$sPostKey];
                }
            }
            $aFields['ext_info'] = serialize($aResponse);
            unset($aResponse);
            $this->api_dibs_updateResultRow($aFields);
            
            if(method_exists($this, 'helper_dibs_hook_callback') && 
                    is_callable(array($this, 'helper_dibs_hook_callback'))) {
                $this->helper_dibs_hook_callback($mOrderInfo);
            }
        }
        else $this->api_dibs_updateResultRow(array('callback_error' => 8));
        exit();
    }
 
    /**
     * Updates from array one order row in dibs results table.
     * 
     * @param array $aFields Key-Value array to update order info in self::$sDibsTable table.
     */
    private function api_dibs_updateResultRow($aFields) {
        if(isset($_POST['orderid']) && !empty($_POST['orderid'])) {
            $sUpdate = '';
            foreach($aFields as $sCell => $sVal) {
                $sUpdate .= "`" . $sCell . "`=" . "'" . self::api_dibs_sqlEncode($sVal) . "',";
            }
            
            $this->helper_dibs_db_write(
                "UPDATE `" . $this->helper_dibs_tools_prefix() . self::api_dibs_get_tableName() . "`
                 SET " . rtrim($sUpdate, ",") . " 
                 WHERE `orderid` = '" . self::api_dibs_sqlEncode($_POST['orderid']) . "' 
                 LIMIT 1;"
            );
        }
    }
    
    /**
     * Simple template loader and renderer. Used to load fallback error template.
     * Support "autotranslate" for self::$aTemplates['autotranslate'] text types.
     * 
     * @param string $sTmplName Name of template to use.
     * @param array $sParams Parameters to replace markers during render.
     * @return string 
     */
    public function api_dibs_renderTemplate($sTmplName, $sParams = array()) {
        $sTmpl = file_get_contents(str_replace('\\', '/', dirname(__FILE__)) . '/' . 
                                   self::$aTemplates['folder'] . '/' . $sTmplName);
        if($sTmpl !== FALSE) {
            foreach($sParams as $sKey => $sVal) {
                $sValueType = substr($sKey, -3);
                if(in_array($sValueType, self::$aTemplates['autotranslate'])) {
                    $sVal = $this->helper_dibs_tools_lang($sVal, $sValueType);
                }
                $sTmpl = str_replace(self::$aTemplates['marker'] . $sKey . self::$aTemplates['marker'], 
                                     $sVal, $sTmpl);
            }
        }
        else $sTmpl = '';
        
        return $sTmpl;
    }
    
    /** DIBS API TOOLS START **/
    /**
     * Calculates MAC for given array of data.
     * 
     * @param array $aData Array of data to calculate the MAC hash.
     * @param string $sHMAC HMAC key for hash calculation.
     * @param bool $bUrlDecode Flag if urldecode before MAC hash calculation is needed (for success action).
     * @return string 
     */
    final public static function api_dibs_calcMAC($aData, $sHMAC, $bUrlDecode = FALSE) {
        $sMAC = '';
        if(!empty($sHMAC)) {
            $sData = '';
            if(isset($aData['MAC'])) unset($aData['MAC']);
            ksort($aData);
            $tData = $aData;
            foreach($aData as $sKey => $sVal) {
                $sData .= '&' . $sKey . '=' . (($bUrlDecode === TRUE) ? urldecode($sVal) : $sVal);
            }
            $sMAC = hash_hmac('sha256', ltrim($sData, '&'), self::api_dibs_hextostr($sHMAC));
        }
        
        return $sMAC;
    }
    
    
    /**
     * Compare calculated MAC with MAC from response urldecode response if second parameter is TRUE.
     * 
     * @param string $sHMAC HMAC key for hash calculation.
     * @param bool $bUrlDecode Flag if urldecode before MAC hash calculation is needed (for success action).
     * @return bool 
     */
    final public static function api_dibs_checkMAC($sHMAC, $bUrlDecode = FALSE) {
        if(!isset($_POST['MAC'])) $_POST['MAC'] = '';
        return ($_POST['MAC'] == self::api_dibs_calcMAC($_POST, $sHMAC, $bUrlDecode)) ? TRUE : FALSE;
    }
    
    /**
     * Returns form action URL of gateway.
     * 
     * @return string 
     */
    final public static function api_dibs_get_formAction() {
        return self::$sFormAction;
    }
    
    /**
     * Returns ISO to DIBS currency array.
     * 
     * @return array 
     */
    final public static function api_dibs_get_currencyArray() {
        return self::$aCurrency;
    }

    /**
     * Getter for table name.
     * 
     * @return string
     */
    final public static function api_dibs_get_tableName() {
        return self::$sDibsTable;
    }
    
    /**
     * Gets value by code from currency array. Supports fliped values.
     * 
     * @param string $sCode Currency code (its ISO formats from self::$aCurrency depends on $bFlip value)
     * @param bool $bFlip If we need to flip self::$aCurrency array and look in another format.
     * @return string 
     */
    final public static function api_dibs_get_currencyMinValue($sCode) {
      
        $minValue = isset(dibs_pw_api::$aCurrency[$sCode]) ? dibs_pw_api::$aCurrency[$sCode][1] : 2;
        return $minValue;      
        
        
    }
    
        
    /**
     * Convert hex HMAC to string.
     * 
     * @param string $sHMAC HMAC key for hash calculation.
     * @return string 
     */
    private static function api_dibs_hextostr($sHMAC) {
        $sRes = '';
        foreach(explode("\n", trim(chunk_split($sHMAC, 2))) as $h) $sRes .= chr(hexdec($h));
        return $sRes;
    }
    
    /**
     * Replaces sql-service quotes to simple quotes and escapes them by slashes.
     * 
     * @param string $sValue Value to escape before SQL operation.
     * @return string 
     */
    public static function api_dibs_sqlEncode($sValue) {
        return addslashes(str_replace('`', "'",  trim(strip_tags((string)$sValue))));
    }
    
    /**
     * Returns integer representation of amount. Saves two signs that are
     * after floating point in float number by multiplication by 100.
     * E.g.: converts to cents in money context.
     * Workarround of float to int casting.
     * 
     * @param float $fNum Float number to round safely.
     * @param int $iPrec Precision. Quantity of digits to save after decimal point.
     * @return int 
     */
    public static function api_dibs_round($fNum, $iPrec = 2) {
        return empty($fNum) ? (int)0 : (int)(string)(round($fNum, $iPrec) * pow(10, $iPrec));
    }
    
    /**
     * Fixes UTF-8 special symbols if encoding of CMS is not UTF-8.
     * Main using is for wided latin alphabets.
     * 
     * @param string $sText The text to prepare in UTF-8 if it is not encoded to it yet.
     * @return string 
     */
    public static function api_dibs_utf8Fix($sText) {
        return (mb_detect_encoding($sText) == 'UTF-8' && mb_check_encoding($sText, 'UTF-8')) ?
               $sText : utf8_encode($sText);
    }
    /** DIBS API TOOLS END **/
}
?>