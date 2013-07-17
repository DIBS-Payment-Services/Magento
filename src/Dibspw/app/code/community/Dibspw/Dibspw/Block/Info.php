<?php
 /**
 * Magento
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * Modifications copyrighted by Dibs A/S, (c) 2012.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Dibspw_Dibspw_Block_Info extends Mage_Payment_Block_Info {
        
    public function __construct() {
        parent::__construct();
    }
    
    
    /**
     * Render the value as an array
     *
     * @param mixed $value
     * @param bool $escapeHtml
     * @return $array
     */
    public function getValueAsArray($value, $escapeHtml = false) {
        if(strpos($value, Mage::helper('dibspw')->__('DIBSPW_LABEL_18')) !== FALSE) $escapeHtml = false;
        
        return parent::getValueAsArray($value, $escapeHtml);
    }
    
    protected function _prepareSpecificInformation($transport = null) {
	$oDataObj = parent::_prepareSpecificInformation();
        $aData = ($this->getIsSecureMode() === FALSE) ? 
                 $this->getMethod()->cms_dibs_getAdminOrderInfo() :
                 $this->getMethod()->cms_dibs_getOrderInfo();
        foreach($aData as $sKey => $sVal) $oDataObj->setData($sKey, $sVal);
	return $oDataObj;
    }
   
}
