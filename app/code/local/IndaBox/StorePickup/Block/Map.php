<?php

class IndaBox_StorePickup_Block_Map extends Mage_Core_Block_Template
{

	public function __construct()
	{	
		parent::__construct();
		
		$this->setTemplate('ibstorepickup/map.phtml');
	}
    
    public function getTosUrl()
    {
        return Mage::helper('ibstorepickup/config')->getTosUrl();
    }
    
    public function getGoogleMaps($callback = null)
    {
        $params = array(
            'v' => '3.exp',
            'region' => 'it',
        );
        if ($apiKey = Mage::helper('ibstorepickup/config')->getGMapsKey())
            $params['key'] = $apiKey;
        if ($callback)
            $params['callback'] = $callback;
        return 'https://maps.googleapis.com/maps/api/js?' . http_build_query($params);
    }
    
    public function getBillingAddress()
    {
        $address = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        $street = $address->getStreet();
        $parts = array(
            (is_array($street) ? implode(', ', $street) : $street) . ',',
            $address->getPostcode(),
            $address->getCity(),
        );
        
        return implode(' ', $parts);
    }
	
}
