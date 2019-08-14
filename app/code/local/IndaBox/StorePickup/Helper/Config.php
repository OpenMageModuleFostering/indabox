<?php

class IndaBox_StorePickup_Helper_Config extends Mage_Core_Helper_Abstract
{

    const API_VERSION = '0.9.3';
    const API_VERSION_HACK = '0.9';
    
    const ENDPOINT_PRODUCTION = 'https://www.indabox.it/api/v:api_version/:api_method.php';
    const ENDPOINT_SANDBOX = 'https://www.indabox.it/sandbox/v:api_version/:api_method.php';

    const XML_PATH_ACCEPT = 'carriers/ibstorepickup/accept';
    const XML_PATH_COST = 'carriers/ibstorepickup/cost';
    const XML_PATH_SANDBOX = 'carriers/ibstorepickup/sandbox';
    const XML_PATH_DEBUG = 'carriers/ibstorepickup/debug';
    const XML_PATH_MERCHANTID = 'carriers/ibstorepickup/merchant_id';
    const XML_PATH_APIKEY = 'carriers/ibstorepickup/api_key';
    const XML_PATH_TOSURL = 'carriers/ibstorepickup/tos_url';
    const XML_PATH_GMAPSKEY = 'carriers/ibstorepickup/gmaps_key';
    const XML_PATH_ALLOWSPECIFIC = 'carriers/ibstorepickup/allowspecific_payment';
    const XML_PATH_SPECIFICPAYMENTS = 'carriers/ibstorepickup/specificpayment';
    
    public function getTosAccepted()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ACCEPT);
    }
    
    public function getMerchantId()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_MERCHANTID);
    }
    
    public function getAllowSpecificPayments()
    {
        return Mage::getStoreConfig(self::XML_PATH_ALLOWSPECIFIC);
    }
    
    public function getSpecificPayments()
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_SPECIFICPAYMENTS));
    }
    
    public function getApiKey()
    {
        return (string) Mage::getStoreConfig(self::XML_PATH_APIKEY);
    }
    
    public function isSandbox()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SANDBOX);
    }
    
    public function isDebug()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DEBUG);
    }
    
    public function getCost()
    {
        return (float) Mage::getStoreConfig(self::XML_PATH_COST);
    }
    
    public function getTosUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_TOSURL);
    }
    
    public function getGMapsKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_GMAPSKEY);
    }
    
    public function getEndpointUrl($method)
    {
        $apiVersion = $method !== 'validMerchant'
            ? self::API_VERSION
            : self::API_VERSION_HACK
        ;
        
        return strtr( ! $this->isSandbox() ? self::ENDPOINT_PRODUCTION : self::ENDPOINT_SANDBOX, array(
            ':api_version' => $apiVersion,
            ':api_method' => $method,
        ));
    }
    
}
