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

$profileTable = $installer->getTable('adyen_subscription/profile');
$connection->addColumn($profileTable, 'stock_id', [
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length' => 5,
    'nullable' => false,
    'unsigned' => true,
    'comment' => 'Stock ID'
]);

$stockTable = $installer->getTable('cataloginventory/stock');
$connection->addForeignKey(
    $installer->getFkName($profileTable, 'stock_id', $stockTable, 'stock_id'),
    $profileTable, 'stock_id', $stockTable, 'stock_id'
);

$installer->endSetup();
