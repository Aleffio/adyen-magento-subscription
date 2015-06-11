<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- Fix created_at and updated_at columns

    ALTER TABLE `{$this->getTable('ho_recurring/profile')}`
        MODIFY `created_at` timestamp NULL,
        MODIFY `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP;

");

$installer->endSetup();
