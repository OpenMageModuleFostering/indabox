<?php

class IndaBox_StorePickup_Model_Points {

    const CACHE_TAG = 'indabox_points';
    const CACHE_KEY = 'indabox_points_%s_%s';
    const CACHE_LIFETIME = 3600; // 1 hour
    
    const POINT_CACHE_KEY = 'indabox_point_%d';
    const POINT_CACHE_LIFETIME = 604800; // 1 week
    
    protected function storePoint($point)
    {
        $cacheKey = sprintf(self::POINT_CACHE_KEY, $point->getId());
        $cache = Mage::app()->getCache();
        $cache->save(serialize($point), $cacheKey, array(self::CACHE_TAG), self::POINT_CACHE_LIFETIME);
    }
    
    public function getPoint($pointId)
    {
        $cacheKey = sprintf(self::POINT_CACHE_KEY, $pointId);
        $cache = Mage::app()->getCache();
        $value = unserialize($cache->load($cacheKey));
        if ( ! $value instanceof IndaBox_StorePickup_Model_Point)
        {
            $value = Mage::getModel('ibstorepickup/point');
            $value->setData(array(
                'pointid' => $pointId,
                'name' => 'Point #' . $pointId,
            ));
        }
        
        return $value;
    }
    
    public function getPoints($address, $radius)
    {
        $cacheKey = sprintf(self::CACHE_KEY, md5($address), $radius);
        $cache = Mage::app()->getCache();
        $value = unserialize($cache->load($cacheKey));
        if ( ! is_array($value) || $value['address'] != $address || $value['radius'] != $radius)
        {
            $points = Mage::getSingleton('ibstorepickup/api')->getPoints($address, $radius);
            foreach ($points as $point)
                $this->storePoint($point);
            
            $value = array(
                'address' => $address,
                'radius' => $radius,
                'location' => $points,
            );
            
            $cache->save(serialize($value), $cacheKey, array(self::CACHE_TAG), self::CACHE_LIFETIME);
        }
        return $value['location'];
    }

}
