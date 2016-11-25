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

class Excellence_Fee_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract{
	protected $_code = 'fee';

	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		
           
		parent::collect($address);

		$this->_setAmount(0);
		$this->_setBaseAmount(0);

		$items = $this->_getAddressItems($address);
		if (!count($items)) {
			return $this; //this makes only address type shipping to come through
		}


		$quote = $address->getQuote();

		if(Excellence_Fee_Model_Fee::canApply($address)){
			$exist_amount = $quote->getFeeAmount();
			$fee = Excellence_Fee_Model_Fee::getFee();
			$balance = $fee - $exist_amount;
			// 			$balance = $fee;

			//$this->_setAmount($balance);
			//$this->_setBaseAmount($balance);

			$address->setFeeAmount($balance);
			$address->setBaseFeeAmount($balance);
				
			$quote->setFeeAmount($balance);

			$address->setGrandTotal($address->getGrandTotal() + $address->getFeeAmount());
			$address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseFeeAmount());
		}
        
	}

	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		    
		$amt = $address->getFeeAmount();
		$address->addTotal(array(
				'code'=>$this->getCode(),
				'title'=>Mage::helper('fee')->__('Fee'),
				'value'=> $amt
		));
		return $this;
        
	}
}