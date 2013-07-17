<?php
class Dibspw_Dibspw_Model_Service_Quote extends Mage_Sales_Model_Service_Quote
{
    public function submitOrder()
    {
        $order = parent::submitOrder();
        // Prevent the cart to be emptied before payment response 
        $this->_quote->setIsActive(true);
        return $order;
    }
}
