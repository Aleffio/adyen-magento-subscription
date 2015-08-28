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
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add entity ID column to subscription quote and subscription order tables

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_quote')}`
		DROP FOREIGN KEY `adyen_subscription_quote_subscription_id`,
        MODIFY `subscription_id` int(11) unsigned NOT NULL;
    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_quote')}`
        DROP PRIMARY KEY,
        ADD COLUMN `entity_id` int(11) unsigned DEFAULT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (`entity_id`),
        ADD CONSTRAINT `adyen_subscription_quote_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `{$installer->getTable('adyen_subscription')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_order')}`
		DROP FOREIGN KEY `adyen_subscription_order_subscription_id`,
        MODIFY `subscription_id` int(11) unsigned NOT NULL;
    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_order')}`
        DROP PRIMARY KEY,
        ADD COLUMN `entity_id` int(11) unsigned DEFAULT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (`entity_id`),
        ADD CONSTRAINT `adyen_subscription_order_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `{$installer->getTable('adyen_subscription')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();
