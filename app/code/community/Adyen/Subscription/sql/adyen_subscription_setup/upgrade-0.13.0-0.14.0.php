<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$subscriptionQuoteTable = $installer->getTable('adyen_subscription/subscription_quote');
$connection->addColumn($subscriptionQuoteTable, 'order_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 10,
    'nullable' => true,
    'unsigned' => true,
    'comment' => 'Order ID'
]);

$orderTable = $installer->getTable('sales/order');
$connection->addForeignKey(
    $installer->getFkName($subscriptionQuoteTable, 'order_id', $orderTable, 'entity_id'),
    $subscriptionQuoteTable, 'order_id', $orderTable, 'entity_id'
);

$quoteTable = $installer->getTable('sales/quote');
$connection->addForeignKey(
    $installer->getFkName($subscriptionQuoteTable, 'quote_id', $quoteTable, 'entity_id'),
    $subscriptionQuoteTable, 'quote_id', $quoteTable, 'entity_id'
);

$connection->addColumn($subscriptionQuoteTable, 'scheduled_at', [
    'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
    'nullable' => true,
    'comment' => 'Scheduled At'
]);

$connection->dropIndex($subscriptionQuoteTable, 'adyen_subscription_quote_quote_id');
$connection->addIndex(
    $subscriptionQuoteTable,
    $installer->getIdxName(
        $subscriptionQuoteTable,
        ['subscription_id', 'quote_id'],
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    ),
    ['subscription_id', 'quote_id'],
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);


$installer->endSetup();
