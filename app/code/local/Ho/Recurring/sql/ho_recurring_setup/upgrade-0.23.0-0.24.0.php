<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$profileLabelTable = $installer->getTable('ho_recurring/product_profile_label');

$connection->dropTable($profileLabelTable);

$table = $connection
    ->newTable($profileLabelTable)
    ->addColumn('label_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'auto_increment' => true,
    ], 'Label ID')
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
    ], 'Profile ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, [
        'unsigned'  => true,
        'nullable'  => false,
    ], 'Store ID')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
        'unsigned'  => true,
        'nullable'  => true,
    ], 'Label')
    ->addForeignKey(
        $installer->getFkName(
            'ho_recurring/product_profile_label',
            'entity_id',
            'ho_recurring/profile',
            'entity_id'
        ),
        'profile_id', $installer->getTable('ho_recurring/product_profile'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'ho_recurring/product_profile_label',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Ho Recurring Product Profile Label');

$connection->createTable($table);

$installer->endSetup();
