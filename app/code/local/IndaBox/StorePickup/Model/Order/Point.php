<?php

class IndaBox_StorePickup_Model_Order_Point extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ibstorepickup/order_point');
    }
    
    public function createFor(Mage_Sales_Model_Order $order, IndaBox_StorePickup_Model_Point $point)
    {
        $this->setData('order_id', $order->getId());
        $this->setData('point_id', $point->getId());
        $this->setData('point_name', $point->getName());
        $this->setData('point_address', $point->getFormattedAddress());
        $this->setData('point_data', serialize($point->getData()));
        $this->setData('is_notified', false);
        return $this->save();
    }
    
    public function getPoint()
    {
        return Mage::getModel('ibstorepickup/point')->setData(unserialize($this->getPointData()));
    }
    
}
