<?php

class IndaBox_StorePickup_Model_Observer
{
	
	public function onSaveShippingMethod($event)
	{
        $quote = $event->getQuote();
        if ($quote->getShippingAddress()->getShippingMethod() !== 'ibstorepickup_ibstorepickup')
        {
            Mage::helper('ibstorepickup')->setUseMethod(false);
            return;
        }
        $request = $event->getRequest();
        $pointId = $request->getPost('indabox_point_id');
        if ( ! $pointId)
        {
            Mage::helper('ibstorepickup')->setUseMethod(false);
            return;
        }
        
        Mage::helper('ibstorepickup')->setPointId($pointId);
        $point = Mage::getSingleton('ibstorepickup/points')->getPoint($pointId);
        
        if ($point->getLatitude()) 
        {
            $address = $quote->getShippingAddress();
            $address->addData($point->getAddressData());
            
            $address->implodeStreetAddress();
            $address->setCollectShippingRates(true);
            
            $quote->collectTotals()->save();
        }
	}	
	
	public function onSaveOrderAfter($event)
	{
		$order = $event->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if ($shippingMethod !== 'ibstorepickup_ibstorepickup')
            return;
            
        $pointId = Mage::helper('ibstorepickup')->getPointId();
        if ( ! $pointId)
            return;

        $point = Mage::getSingleton('ibstorepickup/points')->getPoint($pointId);
		$orderPoint = Mage::getModel('ibstorepickup/order_point');
        $orderPoint->createFor($order, $point);
        
        Mage::helper('ibstorepickup')->setUseMethod(false);
	}
    
    public function onOrderInvoicePay($event)
    {
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();
        
        $orderId = $order->getId();
        $orderPoint = Mage::getModel('ibstorepickup/order_point')->load($orderId, 'order_id');
        if ( ! $orderPoint->getId() || $orderPoint->getIsNotified())
            return;
            
        try {
            $trackingNumber = Mage::getSingleton('ibstorepickup/api')->submitOrder($order, $orderPoint);
        } catch (IndaBox_StorePickup_Exception $e) {
            Mage::logException($e);
            return;
        }
        
        $orderPoint->setIsNotified(true);
        $orderPoint->save();
        
        if ( ! $order->canShip())
            return;
            
        try {
            $shipment = $order->prepareShipment();
            if ($shipment)
            {
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);
            
                $track = Mage::getModel('sales/order_shipment_track')
                     ->setShipment($shipment)
                     ->setData('title', 'IndaBox')
                     ->setData('number', $trackingNumber)
                     ->setData('carrier_code', 'ibstorepickup')
                     ->setData('order_id', $shipment->getData('order_id'))
                 ;
                 
                 $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->addObject($track)
                    ->save()
                ;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
        }
    }

}