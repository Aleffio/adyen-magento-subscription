<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$productProfileTable = $installer->getTable('ho_recurring/profile');

$connection->addColumn($productProfileTable, 'updated_at', [
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'after'     => 'created_at',
    'comment'   => 'Updated At',
]);

$installer->endSetup();
