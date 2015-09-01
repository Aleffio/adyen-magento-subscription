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
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'subscription_fee_amount', "decimal(12,4) null default null");
$installer->getConnection()->addColumn($this->getTable('sales/quote_address'), 'base_subscription_fee_amount', "decimal(12,4) null default null");

$installer->getConnection()->addColumn($this->getTable('sales/order'), 'subscription_fee_amount', "decimal(12,4) null default null");
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'base_subscription_fee_amount', "decimal(12,4) null default null");

$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'subscription_fee_amount', "decimal(12,4) null default null");
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'base_subscription_fee_amount', "decimal(12,4) null default null");

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'subscription_fee_amount', "decimal(12,4) null default null");
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'base_subscription_fee_amount', "decimal(12,4) null default null");

$installer->endSetup();
