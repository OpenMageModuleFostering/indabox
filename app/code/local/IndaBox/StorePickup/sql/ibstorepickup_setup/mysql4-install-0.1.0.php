<?php

$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('ibstorepickup/order_point'))) {
    $installer->run("

CREATE TABLE IF NOT EXISTS `{$installer->getTable('ibstorepickup/order_point')}` (
  `order_point_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Order Point ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Order ID',
  `point_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Point ID',
  `is_notified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is Notified Flag',
  `point_name` text NOT NULL COMMENT 'Point Name',
  `point_address` text NOT NULL COMMENT 'Point Address',
  `point_data` text NOT NULL COMMENT 'Serialized Point Data',
  PRIMARY KEY (`order_point_id`),
  KEY `IDX_IBSP_ORDER_POINT_ORDER_ID` (`order_id`),
  KEY `IDX_IBSP_ORDER_POINT_POINT_ID` (`point_id`),
  CONSTRAINT `FK_IBSP_ORDER_POINT_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES {$installer->getTable('sales/order')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Indabox Storepickup Order to Point';

");

}

$installer->endSetup(); 
