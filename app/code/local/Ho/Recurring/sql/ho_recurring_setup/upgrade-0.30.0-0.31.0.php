<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileTable = $installer->getTable('ho_recurring/product_profile');

$connection->addColumn($profileTable, 'show_on_frontend', [
    'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable'  => false,
    'default'   => 0,
    'comment'   => 'Show on Frontend',
    'after'     => 'price',
]);

$installer->endSetup();
