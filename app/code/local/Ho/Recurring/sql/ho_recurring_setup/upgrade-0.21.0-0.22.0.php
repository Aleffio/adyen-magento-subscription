<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileTable = $installer->getTable('ho_recurring/profile');

$connection->changeColumn($profileTable, 'next_order_at', 'scheduled_at', [
    'type'      => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable'  => true,
    'comment'   => 'Scheduled At',
]);

$installer->endSetup();
