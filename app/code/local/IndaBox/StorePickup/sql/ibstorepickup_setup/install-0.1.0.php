<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('ibstorepickup/order_point'))
    ->addColumn('order_point_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Order Point ID'
    )
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Order ID'
    )
    ->addColumn('point_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Point ID'
    )
    ->addColumn('is_notified', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array(
        'nullable'  => false,
        'unsigned'  => true,
        'default'   => '0',
        ), 'Is Notified Flag'
    )
    ->addColumn('point_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        'default'   => '',
        'length'    => 255,
        ), 'Point Name'
    )
    ->addColumn('point_address', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'default'   => '',
        ), 'Point Address'
    )
    ->addColumn('point_data', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
        'default'   => '',
        ), 'Serialized Point Data'
    )
    ->addIndex($installer->getIdxName('ibstorepickup/order_point', array('order_id')), array('order_id'))
    ->addIndex($installer->getIdxName('ibstorepickup/order_point', array('point_id')), array('point_id'))
    ->addForeignKey(
        $installer->getFkName('ibstorepickup/order_point', 'order_id', 'sales/order', 'entity_id'),
        'order_id', $installer->getTable('sales/order'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Indabox Storepickup Order to Point')
;
$installer->getConnection()->createTable($table);

$installer->endSetup(); 
