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

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/profile')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/profile')}` (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(11) DEFAULT NULL,
      `billing_agreement_id` int(11) DEFAULT NULL,
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/profile_address')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/profile_address')}` (
      `profile_id` int(11) unsigned NOT NULL,
      `address_id` int(10) unsigned NOT NULL,
      UNIQUE KEY `profile_id` (`profile_id`,`address_id`),
      KEY `address_id` (`address_id`),
      CONSTRAINT `adyen_subscription_profile_address_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `adyen_subscription_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_profile_address_address_id` FOREIGN KEY (`address_id`) REFERENCES `customer_address_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/profile_item')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/profile_item')}` (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `profile_id` int(11) unsigned DEFAULT NULL,
      `sku` varchar(255) DEFAULT NULL,
      `name` varchar(255) DEFAULT NULL,
      `price` decimal(12,4) DEFAULT NULL,
      `qty` int(11) DEFAULT NULL,
      `once` int(1) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `status` int(1) DEFAULT NULL,
      PRIMARY KEY (`entity_id`),
      KEY `profile_id` (`profile_id`),
      CONSTRAINT `adyen_subscription_profile_item_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `adyen_subscription_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/profile_quote')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/profile_quote')}` (
      `profile_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `quote_id` int(10) unsigned DEFAULT NULL,
      PRIMARY KEY (`profile_id`),
      KEY `quote_id` (`quote_id`),
      CONSTRAINT `adyen_subscription_profile_quote_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `adyen_subscription_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_profile_quote_quote_id` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/profile_order')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/profile_order')}` (
      `profile_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned DEFAULT NULL,
      PRIMARY KEY (`profile_id`),
      KEY `order_id` (`order_id`),
      CONSTRAINT `adyen_subscription_profile_order_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `adyen_subscription_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_profile_order_order_id   ` FOREIGN KEY (`order_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
