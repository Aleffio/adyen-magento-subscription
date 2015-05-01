<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile table

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        ADD COLUMN `status` varchar(255) AFTER `entity_id`,
        ADD COLUMN `created_at` timestamp AFTER `store_id`,
        ADD COLUMN `ends_at` timestamp AFTER `created_at`,
        ADD COLUMN `customer_name` varchar(255) AFTER `customer_id`;

");

$installer->endSetup();
