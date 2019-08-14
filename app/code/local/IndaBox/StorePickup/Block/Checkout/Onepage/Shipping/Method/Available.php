<?php

class IndaBox_StorePickup_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available {

    protected $_storePickupAvailable = false;

    protected function _filterGroups($groups)
    {
        $useStorePickup = Mage::helper('ibstorepickup')->getUseMethod();
        foreach ($groups as $group => $rates)
        {
            if ($group === 'ibstorepickup' && $useStorePickup)
                $this->_storePickupAvailable = true;
        
            if ($group !== 'ibstorepickup' && $useStorePickup
                || $group === 'ibstorepickup' && ! $useStorePickup
            )
                unset($groups[$group]);
        }
        
        return $groups;
    }

    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();

            $groups = $this->getAddress()->getGroupedAllShippingRates();

            $this->_rates = $this->_filterGroups($groups);
        }
        
        return $this->_rates;
    }
    
    protected function _afterToHtml($html)
    {
        if ($this->_storePickupAvailable)
            $html .= $this->getLayout()->createBlock('ibstorepickup/map')->toHtml();
        
        return $html;
    }

}

