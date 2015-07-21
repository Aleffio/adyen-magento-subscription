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

$productProfileTable = $installer->getTable('adyen_subscription/product_profile');

//product_id
$connection->modifyColumn($productProfileTable, 'product_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'unsigned' => true,
]);

$productTable = $installer->getTable('catalog/product');
$connection->addForeignKey(
    $installer->getFkName($productProfileTable, 'product_id', $productTable, 'entity_id'),
    $productProfileTable, 'product_id', $productTable, 'entity_id'
);

//website_id
$connection->modifyColumn($productProfileTable, 'website_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'unsigned' => true,
]);

$websiteTable = $installer->getTable('core/website');
$connection->addForeignKey(
    $installer->getFkName($productProfileTable, 'website_id', $websiteTable, 'website_id'),
    $productProfileTable, 'website_id', $websiteTable, 'website_id'
);

//customer_group_id
$connection->modifyColumn($productProfileTable, 'customer_group_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'length' => 5,
    'default' => null,
    'nullable' => true,
    'unsigned' => true,
]);

$customerGroupTable = $installer->getTable('customer/customer_group');
$connection->addForeignKey(
    $installer->getFkName($productProfileTable, 'customer_group_id', $customerGroupTable, 'customer_group_id'),
    $productProfileTable, 'customer_group_id', $customerGroupTable, 'customer_group_id'
);

$connection->modifyColumn($productProfileTable, 'label', [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => false,
]);

$connection->modifyColumn($productProfileTable, 'term', [
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'length' => 5,
    'nullable' => false,
]);

$connection->modifyColumn($productProfileTable, 'term_type', [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 40,
    'nullable' => false,
]);

$connection->modifyColumn($productProfileTable, 'min_billing_cycles', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'default' => 0
]);

$connection->modifyColumn($productProfileTable, 'max_billing_cycles', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'default' => false
]);

$connection->modifyColumn($productProfileTable, 'qty', [
    'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'scale'     => 4,
    'precision' => 12,
    'default' => 0,
    'nullable' => false,
]);

$connection->modifyColumn($productProfileTable, 'price', [
    'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'default' => false
]);

$installer->endSetup();
