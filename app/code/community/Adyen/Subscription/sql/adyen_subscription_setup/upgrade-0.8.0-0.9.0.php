<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

    -- DROP TABLE IF EXISTS `{$this->getTable('adyen_subscription/product_subscription')}`;

    CREATE TABLE `{$this->getTable('adyen_subscription/product_subscription')}` (
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
