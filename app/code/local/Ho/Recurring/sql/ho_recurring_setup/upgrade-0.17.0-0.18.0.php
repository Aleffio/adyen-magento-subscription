<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$productProfileTable = $installer->getTable('ho_recurring/product_profile');

$connection->modifyColumn($productProfileTable, 'price', [
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'default'   => false,
    'precision' => 12,
    'scale'     => 4,
]);

$installer->endSetup();
