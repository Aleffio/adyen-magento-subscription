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

    -- Add columns from product subscription to subscription and subscription items tables

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription')}`
        MODIFY `term` int(11),
        ADD COLUMN `term_type` varchar(255) DEFAULT NULL AFTER `term`;

    ALTER TABLE `{$this->getTable('adyen_subscription/subscription_item')}`
        ADD COLUMN `label` varchar(255) DEFAULT NULL AFTER `name`,
        ADD COLUMN `min_billing_cycles` int(11) DEFAULT NULL AFTER `once`,
        ADD COLUMN `max_billing_cycles` int(11) DEFAULT NULL AFTER `min_billing_cycles`;

");

$installer->endSetup();
