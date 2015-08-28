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

$subscriptionQuoteTable = $installer->getTable('adyen_subscription/subscription_address');
$connection->dropTable($subscriptionQuoteTable);
$table = $connection
    ->newTable($subscriptionQuoteTable)
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
        ], 'Item ID')
    ->addColumn('subscription_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, [
        'unsigned'  => true,
        'nullable'  => false,
        ], 'Subscription ID')
    ->addColumn('source', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, [
        'unsigned'  => true,
        'nullable'  => false,
        ], 'Address Source')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Address Type')
    ->addColumn('order_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Order Address ID')
    ->addColumn('customer_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, [
        'unsigned'  => true,
        'nullable'  => true,
        ], 'Customer Address ID')
    ->addIndex(
        $installer->getIdxName(
            'adyen_subscription/subscription_address',
            ['subscription_id', 'type'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        ['subscription_id', 'type'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]
    )
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_address',
            'subscription_id',
            'adyen_subscription/subscription',
            'entity_id'
        ),
        'subscription_id', $installer->getTable('adyen_subscription/subscription'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_address',
            'order_address_id',
            'sales/order_address',
            'entity_id'
        ),
        'order_address_id', $installer->getTable('sales/order_address'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'adyen_subscription/subscription_address',
            'shipping_address_id',
            'customer/address_entity',
            'entity_id'
        ),
        'order_address_id', $installer->getTable('sales/order_address'), 'entity_id',
         Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Adyen Subscription Address');
$connection->createTable($table);

$installer->endSetup();
