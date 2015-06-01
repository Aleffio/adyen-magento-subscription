<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$quoteTable = $installer->getTable('sales/quote');
$connection->addColumn($quoteTable, 'recurring_profile_id', [
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Ho_Recurring Profile ID',
]);

$orderTable = $installer->getTable('sales/order');
$connection->addColumn($orderTable, 'recurring_profile_id', [
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Ho_Recurring Profile ID',
]);

$orderGridTable = $installer->getTable('sales/order_grid');
$connection->addColumn($orderGridTable, 'recurring_profile_id', [
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Ho_Recurring Profile ID',
]);

$installer->endSetup();
