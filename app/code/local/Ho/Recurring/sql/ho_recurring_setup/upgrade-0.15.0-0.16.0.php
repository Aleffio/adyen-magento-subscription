<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileQuoteTable = $installer->getTable('ho_recurring/profile_address');
$connection->dropTable($profileQuoteTable);
$table = $connection
    ->newTable($profileQuoteTable)
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ], 'Item ID')
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        ], 'Profile ID')
    ->addColumn('source', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, [
        'unsigned'  => true,
        'nullable'  => false,
        ], 'Address Source')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Address Type')
    ->addColumn('order_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Order Address ID')
    ->addColumn('customer_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Customer Address ID')
    ->addIndex(
        $installer->getIdxName(
            'ho_recurring/profile_address',
            ['profile_id', 'type'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        ['profile_id', 'type'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName(
            'ho_recurring/profile_address',
            'profile_id',
            'ho_recurring/profile',
            'entity_id'
        ),
        'profile_id', $installer->getTable('ho_recurring/profile'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'ho_recurring/profile_address',
            'order_address_id',
            'sales/order_address',
            'entity_id'
        ),
        'order_address_id', $installer->getTable('sales/order_address'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'ho_recurring/profile_address',
            'shipping_address_id',
            'customer/address_entity',
            'entity_id'
        ),
        'order_address_id', $installer->getTable('sales/order_address'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Ho Recurring Address');
$connection->createTable($table);

$installer->endSetup();
