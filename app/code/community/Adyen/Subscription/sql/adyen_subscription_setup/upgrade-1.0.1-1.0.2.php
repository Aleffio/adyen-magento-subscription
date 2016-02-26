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

$orderAddressTable = $installer->getTable('sales/order_address');

if (! $connection->tableColumnExists($orderAddressTable, 'save_in_address_book')) {
    $connection->addColumn($orderAddressTable, 'save_in_address_book', [
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length' => 1,
        'unsigned' => true,
        'nullable' => true,
        'comment' => 'Save In Address Book',
    ]);
}

if (! $connection->tableColumnExists($orderAddressTable, 'same_as_billing')) {
    $connection->addColumn($orderAddressTable, 'same_as_billing', [
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'length' => 1,
        'unsigned' => true,
        'nullable' => true,
        'comment' => 'Same As Billing',
    ]);
}

$installer->endSetup();
