<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileQuoteTable = $installer->getTable('ho_recurring/profile_quote');
$connection->modifyColumn($profileQuoteTable, 'quote_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'unsigned' => true,
]);

$installer->endSetup();
