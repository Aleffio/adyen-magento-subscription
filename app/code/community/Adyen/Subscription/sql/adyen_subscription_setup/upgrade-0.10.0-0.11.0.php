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

$productSubscriptionTable = $installer->getTable('adyen_subscription/product_subscription');

//product_id
$connection->modifyColumn($productSubscriptionTable, 'product_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'unsigned' => true,
]);

$productTable = $installer->getTable('catalog/product');
$connection->addForeignKey(
    $installer->getFkName($productSubscriptionTable, 'product_id', $productTable, 'entity_id'),
    $productSubscriptionTable, 'product_id', $productTable, 'entity_id'
);

//website_id
$connection->modifyColumn($productSubscriptionTable, 'website_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'unsigned' => true,
]);

$websiteTable = $installer->getTable('core/website');
$connection->addForeignKey(
    $installer->getFkName($productSubscriptionTable, 'website_id', $websiteTable, 'website_id'),
    $productSubscriptionTable, 'website_id', $websiteTable, 'website_id'
);

//customer_group_id
$connection->modifyColumn($productSubscriptionTable, 'customer_group_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'length' => 5,
    'default' => null,
    'nullable' => true,
    'unsigned' => true,
]);

$customerGroupTable = $installer->getTable('customer/customer_group');
$connection->addForeignKey(
    $installer->getFkName($productSubscriptionTable, 'customer_group_id', $customerGroupTable, 'customer_group_id'),
    $productSubscriptionTable, 'customer_group_id', $customerGroupTable, 'customer_group_id'
);

$connection->modifyColumn($productSubscriptionTable, 'label', [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'nullable' => false,
]);

$connection->modifyColumn($productSubscriptionTable, 'term', [
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'length' => 5,
    'nullable' => false,
]);

$connection->modifyColumn($productSubscriptionTable, 'term_type', [
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 40,
    'nullable' => false,
]);

$connection->modifyColumn($productSubscriptionTable, 'min_billing_cycles', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'default' => 0
]);

$connection->modifyColumn($productSubscriptionTable, 'max_billing_cycles', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'default' => false
]);

$connection->modifyColumn($productSubscriptionTable, 'qty', [
    'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'scale'     => 4,
    'precision' => 12,
    'default' => 0,
    'nullable' => false,
]);

$connection->modifyColumn($productSubscriptionTable, 'price', [
    'type' => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'default' => false
]);

$installer->endSetup();
