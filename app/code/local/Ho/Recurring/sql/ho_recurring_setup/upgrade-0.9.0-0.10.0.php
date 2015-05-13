<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Add columns from product profile to profile and profile items tables

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        MODIFY `term` int(11),
        ADD COLUMN `term_type` varchar(255) DEFAULT NULL AFTER `term`;

    ALTER TABLE `{$this->getTable('ho_recurring/profile_item')}`
        ADD COLUMN `label` varchar(255) DEFAULT NULL AFTER `name`,
        ADD COLUMN `min_billing_cycles` int(11) DEFAULT NULL AFTER `once`,
        ADD COLUMN `max_billing_cycles` int(11) DEFAULT NULL AFTER `min_billing_cycles`;

");

$installer->endSetup();
