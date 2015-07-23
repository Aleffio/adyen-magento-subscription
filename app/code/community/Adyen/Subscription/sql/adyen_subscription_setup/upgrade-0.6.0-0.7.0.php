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

$installer->run("

    -- Add columns to subscription item table

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_item')}`
        DROP COLUMN `status`,
        ADD COLUMN `status` varchar(255) DEFAULT NULL AFTER `entity_id`,
        ADD COLUMN `price_incl_tax` decimal(12,4) DEFAULT NULL AFTER `price`;

");

$installer->endSetup();
