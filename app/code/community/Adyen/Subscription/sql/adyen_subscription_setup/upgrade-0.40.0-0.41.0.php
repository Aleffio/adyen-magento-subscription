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
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, Sander Mangel <sander@sandermangel.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

// Add description column to history table
$connection->addColumn($this->getTable('adyen_subscription/subscription_history'), 'description', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'default'   => '',
    'comment'   => 'Subscription change description',
));

$installer->endSetup();
