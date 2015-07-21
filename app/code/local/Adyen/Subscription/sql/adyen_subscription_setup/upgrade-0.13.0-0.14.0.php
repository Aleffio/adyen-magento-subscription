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

$profileQuoteTable = $installer->getTable('adyen_subscription/profile_quote');
$connection->addColumn($profileQuoteTable, 'order_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 10,
    'nullable' => true,
    'unsigned' => true,
    'comment' => 'Order ID'
]);

$orderTable = $installer->getTable('sales/order');
$connection->addForeignKey(
    $installer->getFkName($profileQuoteTable, 'order_id', $orderTable, 'entity_id'),
    $profileQuoteTable, 'order_id', $orderTable, 'entity_id'
);

$connection->dropIndex($profileQuoteTable, 'quote_id');
$quoteTable = $installer->getTable('sales/quote');
$connection->addForeignKey(
    $installer->getFkName($profileQuoteTable, 'quote_id', $quoteTable, 'entity_id'),
    $profileQuoteTable, 'quote_id', $quoteTable, 'entity_id'
);

$connection->addColumn($profileQuoteTable, 'scheduled_at', [
    'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'nullable' => true,
    'comment' => 'Scheduled At'
]);

$connection->dropIndex($profileQuoteTable, 'adyen_subscription_profile_quote_quote_id');
$connection->addIndex(
    $profileQuoteTable,
    $installer->getIdxName(
        $profileQuoteTable,
        ['profile_id', 'quote_id'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    ['profile_id', 'quote_id'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);


$installer->endSetup();
