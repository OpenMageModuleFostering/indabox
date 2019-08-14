<?php

class IndaBox_StorePickup_Model_Source_Selectorpayment
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('ibstorepickup')->__('All Allowed Payments')),
            array('value' => 1, 'label' => Mage::helper('ibstorepickup')->__('Specific Payments')),
        );
    }
}
