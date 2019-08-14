<?php

class IndaBox_StorePickup_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function setUseMethod($flag)
    {
        $data = array(
            'use_storepickup' => $flag,
        );
        $session = Mage::getSingleton('checkout/session');
        $session->setData('indabox_storepickup', $data);	
    }
    
    public function getUseMethod()
    {
        $session = Mage::getSingleton('checkout/session');
        $data = $session->getData('indabox_storepickup');
        return is_array($data) && $data['use_storepickup'];
    }
    
    public function setPointId($pointId)
    {
        $this->setUseMethod(true);
        $session = Mage::getSingleton('checkout/session');
        $data = $session->getData('indabox_storepickup');
        $data['point_id'] = $pointId;
        $session->setData('indabox_storepickup', $data);	
    }
    
    public function getPointId()
    {
        $session = Mage::getSingleton('checkout/session');
        $data = $session->getData('indabox_storepickup');
        if ( ! is_array($data) || ! isset($data['point_id']))
            return 0;
        else
            return $data['point_id'];
    }
	
	public function getChangeMethodUrl()
	{
		return $this->_getUrl('ibstorepickup/index/changemethod', array('_secure' => true));		
	}
    
    public function getSearchUrl()
	{
		return $this->_getUrl('ibstorepickup/index/search', array('_secure' => true));		
	}
    
    public function getLocationUrl()
	{
		return $this->_getUrl('ibstorepickup/index/location', array('_secure' => true));		
	}
    
    public function getMediaUrl()
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) . 'indabox/';
	}
    
	public function getCustomerAddress()
	{
		$cSession = Mage::getSingleton('customer/session');

		$attribute = Mage::getModel("eav/entity_attribute")->load("customer_shipping_address_id","attribute_code");
					
		if($cSession->isLoggedIn() && $attribute->getId())
		{
			$address = Mage::helper('accountfield')
						->getShippingAddressByCustomerId($cSession->getCustomer()->getId());			
			if($address)
				return $address;
		}
		
		$cart = Mage::getSingleton('checkout/cart');
		return $cart->getQuote()->getShippingAddress();
	}
}
