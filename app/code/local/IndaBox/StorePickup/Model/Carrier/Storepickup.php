<?php

class IndaBox_StorePickup_Model_Carrier_Storepickup
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'ibstorepickup';

   
	public function getCode()
	{
		return $this->_code;
	}
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {		
		if ( ! $this->getConfigFlag('active'))
            return false;
            
        if ( ! Mage::helper('ibstorepickup/config')->getTosAccepted())
            return false;
		
		$items = $request->getAllItems();
		if ( ! count($items))
			return false;
            
        if ($request->getDestCountryId() !== 'IT')
            return false;
            
        if ($request->getPackageWeight() > $this->getConfigData('maximum_weight'))
            return false;
            
        if ($request->getBaseSubtotalInclTax() > $this->getConfigData('maximum_subtotal'))
            return false;
            
        $api = Mage::getSingleton('ibstorepickup/api');
        try {
            $result = $api->validateMerchant();
        } catch (IndaBox_StorePickup_Exception $e) {
            return false;
        }
        if ( ! $result)
            return false;
            
        $cost = Mage::helper('ibstorepickup/config')->getCost();
            
		$result = Mage::getModel('shipping/rate_result');
		$method = Mage::getModel('shipping/rate_result_method');
		$method->setCarrier('ibstorepickup');
		$method->setCarrierTitle($this->getConfigData('title'));
		$method->setMethod('ibstorepickup');
		$method->setMethodTitle('');
		$method->setPrice($cost);
		$method->setCost($cost);
		$result->append($method);
		
		return $result;
    }

    public function getAllowedMethods()
    {
        return array(
            'ibstorepickup' => 'ibstorepickup',
        );
    }
    
    public function getTrackingInfo($tracking)
    {
        $info = array();

        $result = $this->getTracking($tracking);

        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                return $trackings[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }
    
    public function getTracking($trackings)
    {
        if ( ! is_array($trackings))
            $trackings = array($trackings);
            
        $errorTitle = Mage::helper('ibstorepickup')->__('Unable to retrieve tracking');
    
        $raw = Mage::getSingleton('ibstorepickup/api')->trackShipment($trackings);
        
        $success = array();
        $error = array();
        foreach ($raw as $idx => $row)
            if ($row['esiste'])
            {
                list($date, $time) = explode(' ', $row['data']);
                $success[$idx] = array(
                    'deliverydate' => $date,
                    'deliverytime' => $time,
                    'status' => $row['stato'],
                );
            }
            else
                $error[$idx] = true;
                
        $result = Mage::getModel('shipping/tracking_result');
        if ($success || $error) {
            foreach ($error as $t => $r) {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier('ibstorepickup');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);
            }

            foreach ($success as $t => $data) {
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier('ibstorepickup');
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($t);
                $tracking->addData($data);

                $result->append($tracking);
            }
        } else {
            foreach ($trackings as $t) {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier('ibstorepickup');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

            }
        }
        
        return $result;
    }
    
}
