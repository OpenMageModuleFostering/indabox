<?php

class IndaBox_StorePickup_Model_Resource_Order_Point extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('ibstorepickup/order_point', 'order_point_id');
    }
}
