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

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/subscription')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/subscription')}` (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(11) DEFAULT NULL,
      `billing_agreement_id` int(11) DEFAULT NULL,
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/subscription_address')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/subscription_address')}` (
      `subscription_id` int(11) unsigned NOT NULL,
      `address_id` int(10) unsigned NOT NULL,
      UNIQUE KEY `subscription_id` (`subscription_id`,`address_id`),
      KEY `address_id` (`address_id`),
      CONSTRAINT `adyen_subscription_subscription_address_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `adyen_subscription_subscription` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_subscription_address_address_id` FOREIGN KEY (`address_id`) REFERENCES `customer_address_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/subscription_item')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/subscription_item')}` (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `subscription_id` int(11) unsigned DEFAULT NULL,
      `sku` varchar(255) DEFAULT NULL,
      `name` varchar(255) DEFAULT NULL,
      `price` decimal(12,4) DEFAULT NULL,
      `qty` int(11) DEFAULT NULL,
      `once` int(1) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT NULL,
      `status` int(1) DEFAULT NULL,
      PRIMARY KEY (`entity_id`),
      KEY `subscription_id` (`subscription_id`),
      CONSTRAINT `adyen_subscription_subscription_item_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `adyen_subscription_subscription` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/subscription_quote')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/subscription_quote')}` (
      `subscription_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `quote_id` int(10) unsigned DEFAULT NULL,
      PRIMARY KEY (`subscription_id`),
      KEY `quote_id` (`quote_id`),
      CONSTRAINT `adyen_subscription_subscription_quote_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `adyen_subscription_subscription` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_subscription_quote_quote_id` FOREIGN KEY (`quote_id`) REFERENCES `sales_flat_quote` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/subscription_order')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/subscription_order')}` (
      `subscription_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `order_id` int(10) unsigned DEFAULT NULL,
      PRIMARY KEY (`subscription_id`),
      KEY `order_id` (`order_id`),
      CONSTRAINT `adyen_subscription_subscription_order_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `adyen_subscription_subscription` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
      CONSTRAINT `adyen_subscription_subscription_order_order_id   ` FOREIGN KEY (`order_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
