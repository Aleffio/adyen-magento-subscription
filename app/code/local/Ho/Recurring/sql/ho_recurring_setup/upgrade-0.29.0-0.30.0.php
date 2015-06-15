<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileTable = $installer->getTable('ho_recurring/profile');

$connection->addColumn($profileTable, 'cancel_code', [
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => true,
    'comment'   => 'Cancel Reason Code',
    'length'    => 255,
]);

$installer->endSetup();
