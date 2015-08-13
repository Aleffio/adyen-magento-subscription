<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$subscriptionHistoryTable = $installer->getTable('adyen_subscription/subscription_history');

$connection->dropTable($subscriptionHistoryTable);

$table = $connection
    ->newTable($subscriptionHistoryTable)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'auto_increment' => true,
    ], 'History ID')
    ->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
    ], 'Subscription ID')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => true,
    ], 'Admin User ID')
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => true,
    ], 'Customer ID')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
        'unsigned'  => true,
        'nullable'  => false,
    ], 'Status')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, [
        'unsigned'  => true,
        'nullable'  => false,
    ], 'Status Change Code')
    ->addColumn('date', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, [
        'nullable' => false,
    ], 'Date')
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_history',
            'subscription_id',
            'adyen_subscription/subscription',
            'entity_id'
        ),
        'subscription_id', $installer->getTable('adyen_subscription/subscription'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_history',
            'user_id',
            'admin_user',
            'user_id'
        ),
        'user_id', $installer->getTable('admin_user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_history',
            'customer_id',
            'customer_entity',
            'entity_id'
        ),
        'customer_id', $installer->getTable('customer_entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Adyen Subscription History');

$connection->createTable($table);

$installer->endSetup();
