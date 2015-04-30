<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile table

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        ADD COLUMN `customer_id` int(10) unsigned DEFAULT NULL AFTER `entity_id`,
        ADD COLUMN `store_id` smallint(5) unsigned DEFAULT NULL AFTER `billing_agreement_id`,
        ADD COLUMN `payment_method` varchar(255) DEFAULT NULL AFTER `store_id`,
        ADD COLUMN `shipping_method` varchar(255) DEFAULT NULL AFTER `payment_method`,
        ADD CONSTRAINT `ho_recurring_profile_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
        ADD CONSTRAINT `ho_recurring_profile_store_id` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE;

");

$installer->endSetup();
