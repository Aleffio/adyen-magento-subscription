<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns to profile table

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        ADD COLUMN `error_message` varchar(255) DEFAULT NULL AFTER `status`;

");

$installer->endSetup();
