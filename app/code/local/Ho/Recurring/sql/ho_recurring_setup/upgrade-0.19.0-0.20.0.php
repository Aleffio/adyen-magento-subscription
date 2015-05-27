<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileAddressTable = $installer->getTable('ho_recurring/profile_address');

$connection->modifyColumn($profileAddressTable, 'item_id', [
    'type'           => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'auto_increment' => true,
    'unsigned'       => true,
]);

$installer->endSetup();
