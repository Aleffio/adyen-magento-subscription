<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add product_id column to profile item table

    ALTER TABLE `{$this->getTable('ho_recurring/profile_item')}`
        ADD COLUMN `product_id` int(10) unsigned DEFAULT NULL AFTER `profile_id`,
        ADD CONSTRAINT `ho_recurring_profile_item_product_id` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE;

");

$installer->endSetup();
