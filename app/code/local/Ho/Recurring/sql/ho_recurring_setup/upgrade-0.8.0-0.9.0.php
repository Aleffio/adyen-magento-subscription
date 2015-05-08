<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- DROP TABLE IF EXISTS `{$this->getTable('ho_recurring/product_profile')}`;

    CREATE TABLE `{$this->getTable('ho_recurring/product_profile')}` (
      `entity_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `product_id` int(10) unsigned DEFAULT NULL,
      `label` varchar(255) DEFAULT NULL,
      `website_id` int(11) DEFAULT 0,
      `customer_group_id` int(11) DEFAULT 0,
      `term` int(11) DEFAULT 0,
      `term_type` varchar(255) DEFAULT NULL,
      `min_billing_cycles` int(11) DEFAULT 0,
      `max_billing_cycles` int(11) DEFAULT 0,
      `qty` int(11) DEFAULT 0,
      `price` decimal(12,4) DEFAULT 0,
      `sort_order` int(11) DEFAULT 0,
      PRIMARY KEY (`entity_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
