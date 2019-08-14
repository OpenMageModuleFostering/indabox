<?php

class IndaBox_StorePickup_IndexController extends Mage_Core_Controller_Front_Action
{

	public function changemethodAction()
	{	
        $flag = (bool) $this->getRequest()->getParam('flag');
        Mage::helper('ibstorepickup')->setUseMethod($flag);
	}
	
    public function searchAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
    
        $response = new Varien_Object();
        $response->setError(false);
        
        $address = (string) $this->getRequest()->getParam('address', '');
        $radius = (string) $this->getRequest()->getParam('radius', '');
        
        if (empty($address) || $radius <= 0)
        {
            $response->setError(true);
            $response->setMessage($this->__('Invalid search parameters.'));
            
            $this->getResponse()->setBody($response->toJson());
            return;
        }
        
        try {
            $points = Mage::getSingleton('ibstorepickup/points')->getPoints($address, $radius);
            $list = array();
            foreach ($points as $point)
                $list[] = $point->toArray();
            $response->setPoints($list);
        } catch (IndaBox_StorePickup_Exception $e) {
            $response->setError(true);
            $response->setMessage($this->__('Error while communicating with IndaBox. Please retry later.'));
            $this->getResponse()->setBody($response->toJson());
        }
        
         $this->getResponse()->setBody($response->toJson());
    }
    
    public function locationAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
    
        $response = new Varien_Object();
        $response->setError(false);
        
        $address = (string) $this->getRequest()->getParam('address', '');
        
        if (empty($address))
        {
            $response->setError(true);
            $response->setMessage($this->__('Invalid search parameters.'));
            
            $this->getResponse()->setBody($response->toJson());
            return;
        }
        
        $location = Mage::getSingleton('ibstorepickup/googleMaps')->getLocation($address);
        if ($location !== null)
        {
            $response->setLatitude($location[0]);
            $response->setLongitude($location[1]);
        }
        else
        {
            $response->setError(true);
            $response->setMessage($this->__('Location does not found'));
        }
        
         $this->getResponse()->setBody($response->toJson());
    }
    
}
