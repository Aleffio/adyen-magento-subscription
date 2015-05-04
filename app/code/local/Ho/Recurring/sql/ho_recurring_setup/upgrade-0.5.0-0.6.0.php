<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile table

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        ADD COLUMN `term` varchar(255) AFTER `ends_at`,
        ADD COLUMN `next_order_at` timestamp AFTER `term`;

");

$installer->endSetup();
