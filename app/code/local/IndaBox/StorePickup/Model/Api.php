<?php

class IndaBox_StorePickup_Model_Api {

    protected function _hideApiKey($debugData, $apiKey)
    {
        if (is_array($debugData) && strlen($apiKey)) {
            foreach ($debugData as $key => &$value) {
                if (is_string($value))
                    $value = str_replace($apiKey, '******', $value);
                else
                    $value = $this->_hideApiKey($value, $apiKey);
            }
        }
        return $debugData;
    }

    protected function _debug($debugData)
    {
        if (Mage::helper('ibstorepickup/config')->isDebug()) {
            $debugData = $this->_hideApiKey($debugData, Mage::helper('ibstorepickup/config')->getApiKey());
            Mage::getModel('core/log_adapter', 'indabox_storepickup.log')
               ->log($debugData);
        }
    }

    protected function _getToken($merchantId, $apiKey, $body)
    {
        return hash_hmac("sha256", $body, $merchantId . $apiKey);
    }

    public function call($method, $params)
    {
        $config = Mage::helper('ibstorepickup/config');
        $merchantId = $config->getMerchantId();
        if (empty($merchantId))
            throw new IndaBox_StorePickup_Exception(Mage::helper('ibstorepickup')->__('Please specify Merchant ID'));
        $apiKey = $config->getApiKey();
        if (empty($apiKey))
            throw new IndaBox_StorePickup_Exception(Mage::helper('ibstorepickup')->__('Please specify API Key'));
        
        $body = json_encode($params);
        $post = array(
            'id_merchant' => $merchantId,
            'msg' => $body,
            'token' => $this->_getToken($merchantId, $apiKey, $body),
        );
        
        $this->_debug($post);
        
        $client = new Zend_Http_Client($config->getEndpointUrl($method));
        $client->setParameterPost($post);
        $client->setHeaders(array('Accept-encoding: identity'));
        $client->setConfig(array('strictredirects' => true));

        try {
            $response = $client->request(Zend_Http_Client::POST);
        } catch (Zend_Http_Client_Exception $e) {
            Mage::logException($e);
            throw new IndaBox_StorePickup_Exception(Mage::helper('ibstorepickup')->__('Could not communicate with server'));
        }
        $body = $response->getBody();
        
        $json = json_decode($body, true);
        if ($json === null)
        {
            $this->_debug($body);
            throw new IndaBox_StorePickup_Exception(Mage::helper('ibstorepickup')->__('Invalid server reply'));
        }
        
        $this->_debug($json);
            
        return $json;
    }
    
    public function validateMerchant()
    {
        $apiKey = Mage::helper('ibstorepickup/config')->getApiKey();
        $result = $this->call('validMerchant', array(
            'api_key' => $apiKey,
        ));
        
        return $result === 'OK';
    }
    
    public function getPoints($address, $radius)
    {
        $points = $this->call('getPoints', array(
            'search' => $address,
            'radius' => $radius,
        ));
        
        if ( ! is_array($points))
            return array();
         
        $result = array();
        foreach ($points as $data)
        {
            $point = Mage::getModel('ibstorepickup/point');
            $point->setData($data);
            $result[] = $point;
        }
        
        return $result;
    }
    
    public function submitOrder(Mage_Sales_Model_Order $order, IndaBox_StorePickup_Model_Order_Point $orderPoint)
    {
        $billingAddress = $order->getBillingAddress();
        $result = $this->call('submitOrder', array(
            'ref' => $order->getId(),
            'ibp' => array(
                'pointid' => $orderPoint->getPointId(),
                'name' => $orderPoint->getPointName(),
            ),
            'rcv' => array(
                'firstName' => $billingAddress->getFirstname(),
                'surname' => $billingAddress->getLastname(),
                'street' => $billingAddress->getStreetFull(),
                'postalCode' => $billingAddress->getPostcode(),
                'city' => $billingAddress->getCity(),
                'country' => $billingAddress->getCountryId(),
                'email' => $order->getCustomerEmail(),
                'mobile' => str_replace('+', '', preg_replace('#\+\.\.#', '', $billingAddress->getTelephone())),
            ),
            'pcl' => array(
                'weight' => $order->getWeight(),
                'orderNumber' => $order->getIncrementId(),
                'orderDate' => $order->getCreatedAt(),
            ),
        ));
        
        if ( ! is_array($result) || ! isset($result['codice']))
            throw new IndaBox_StorePickup_Exception(Mage::helper('ibstorepickup')->__('Could not submit order'));
            
        return $result['codice'];
    }
    
    public function trackShipment($trackNumber)
    {
        $rows = $this->call('getShipmentStatusbyTid', array(
            'transaction_ids' => is_array($trackNumber) ? $trackNumber : array($trackNumber),
        ));
        
        $result = array();
        foreach ($rows as $row)
            $result[$row['ref']] = $row;
        
        return $result;
    }
    
}
