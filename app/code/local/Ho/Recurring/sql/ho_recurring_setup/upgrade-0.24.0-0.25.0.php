<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileLabelTable = $installer->getTable('ho_recurring/product_profile_label');

$connection->addIndex(
    $profileLabelTable,
    $installer->getIdxName(
        $profileLabelTable,
        array('profile_id', 'store_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    array('profile_id', 'store_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
