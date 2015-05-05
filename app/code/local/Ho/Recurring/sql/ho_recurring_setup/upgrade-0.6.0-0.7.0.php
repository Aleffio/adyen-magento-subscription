<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile item table

    ALTER TABLE `{$this->getTable('ho_recurring/profile_item')}`
        DROP COLUMN `status`,
        ADD COLUMN `status` varchar(255) DEFAULT NULL AFTER `entity_id`,
        ADD COLUMN `price_incl_tax` decimal(12,4) DEFAULT NULL AFTER `price`;

");

$installer->endSetup();
