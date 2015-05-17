<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileTable = $installer->getTable('ho_recurring/profile');
$connection->addColumn($profileTable, 'stock_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 5,
    'nullable' => false,
    'unsigned' => true,
    'comment' => 'Stock ID'
]);

$stockTable = $installer->getTable('cataloginventory/stock');
$connection->addForeignKey(
    $installer->getFkName($profileTable, 'stock_id', $stockTable, 'stock_id'),
    $profileTable, 'stock_id', $stockTable, 'stock_id'
);

$installer->endSetup();
