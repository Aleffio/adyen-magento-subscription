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

$installer->run("

    -- Add columns to subscription table

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription')}`
        ADD COLUMN `customer_id` int(10) unsigned DEFAULT NULL AFTER `entity_id`,
        ADD COLUMN `store_id` smallint(5) unsigned DEFAULT NULL AFTER `billing_agreement_id`,
        ADD COLUMN `payment_method` varchar(255) DEFAULT NULL AFTER `store_id`,
        ADD COLUMN `shipping_method` varchar(255) DEFAULT NULL AFTER `payment_method`,
        ADD CONSTRAINT `adyen_subscription_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
        ADD CONSTRAINT `adyen_subscription_store_id` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE;

");

$installer->endSetup();
