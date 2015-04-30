<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add entity ID column to profile quote and profile order tables

    ALTER TABLE `{$this->getTable('ho_recurring/profile_quote')}`
		DROP FOREIGN KEY `ho_recurring_profile_quote_profile_id`,
        MODIFY `profile_id` int(11) unsigned NOT NULL;
    ALTER TABLE `{$this->getTable('ho_recurring/profile_quote')}`
        DROP PRIMARY KEY,
        ADD COLUMN `entity_id` int(11) unsigned DEFAULT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (`entity_id`),
        ADD CONSTRAINT `ho_recurring_profile_quote_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `ho_recurring_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

    ALTER TABLE `{$this->getTable('ho_recurring/profile_order')}`
		DROP FOREIGN KEY `ho_recurring_profile_order_profile_id`,
        MODIFY `profile_id` int(11) unsigned NOT NULL;
    ALTER TABLE `{$this->getTable('ho_recurring/profile_order')}`
        DROP PRIMARY KEY,
        ADD COLUMN `entity_id` int(11) unsigned DEFAULT NULL AUTO_INCREMENT FIRST,
        ADD PRIMARY KEY (`entity_id`),
        ADD CONSTRAINT `ho_recurring_profile_order_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `ho_recurring_profile` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

");

$installer->endSetup();
