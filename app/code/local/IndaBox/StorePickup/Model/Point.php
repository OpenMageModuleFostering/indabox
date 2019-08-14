<?php

class IndaBox_StorePickup_Model_Point extends Varien_Object {

    public function getId()
    {
        return $this->_getData('pointid');
    }
    
    public function getName()
    {
        return $this->_getData('name');
    }
    
    public function getStreet()
    {
        $parts = array();
        foreach (array('toponimo', 'indirizzo', 'civico') as $key)
        {
            $value = trim($this->_getData($key));
            if ( ! empty($value))
            {
                if ($key === 'civico')
                    $value = ', ' . $value;
                else
                    $value = ' ' . $value;
                $parts[] = $value;
            }
        }
        return trim(implode('', $parts));
    }
    
    public function getCity()
    {
        return $this->_getData('comune');
    }
    
    public function getPostcode()
    {
        return $this->_getData('cap');
    }
    
    public function getRegion()
    {
        return $this->_getData('provincia');
    }
    
    public function getRegionId()
    {
        $regionModel = Mage::getModel('directory/region')->loadByCode($this->getRegion(), $this->getCountryId());
        return $regionModel->getId();
    }
    
    public function getCountryId()
    {
        // hardcoded
        return 'IT';
    }
    
    public function getTelephone()
    {
        return $this->_getData('tel');
    }
    
    public function getLatitude()
    {
        return $this->_getData('lat');
    }
    
    public function getLongitude()
    {
        return $this->_getData('lng');
    }
    
    public function getDistance()
    {
        return $this->_getData('distance');
    }
    
    public function getIndex()
    {
        return $this->_getData('indice');
    }
    
    public function getHours()
    {
        return $this->_getData('orari');
    }
    
    public function getUri()
    {
        return $this->_getData('uri');
    }
    
    public function getAddressData()
    {
        return array(
            'firstname' => Mage::helper('ibstorepickup')->__('IndaBox Point'),
			'lastname' => $this->getName(),
			'street' => $this->getStreet(),
			'city' => $this->getCity(),
			'region' => $this->getRegion(),
			'region_id' => $this->getRegionId(),
			'postcode' => $this->getPostcode(),
			'country_id' => $this->getCountryId(),
			'telephone' => $this->getTelephone(),
        );
    }
    
    public function getFormattedAddress()
    {
        $address = Mage::getModel('customer/address');
        $address->setData($this->getAddressData());
        
        return $address->format('oneline');
    }
    
    public function toArray(array $arrAttributes = array())
    {
        $result = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
        );
        $result = array_merge($result, $this->getAddressData());
        $result = array_merge($result, array(
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'distance' => $this->getDistance(),
            'url' => $this->getUri(),
            'hours' => $this->getHours(),
        ));
        
        return $result;
    }
    
}
