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

    -- Add product_id column to subscription item table

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_item')}`
        ADD COLUMN `product_id` int(10) unsigned DEFAULT NULL AFTER `subscription_id`,
        ADD CONSTRAINT `adyen_subscription_item_product_id` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE;

");

$installer->endSetup();
