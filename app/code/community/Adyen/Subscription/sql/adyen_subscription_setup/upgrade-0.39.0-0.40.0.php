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

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

// Add column to grid table to sales/order_grid
$connection->addColumn($installer->getTable('sales/order_grid'), 'created_subscription_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => '10',
    'comment'   => 'Created Subscription ID',
));

// Add key to table for this field,
// it will improve the speed of searching & sorting by the field
$this->getConnection()->addKey(
    $this->getTable('sales/order_grid'),
    'created_subscription_id',
    'created_subscription_id'
);

// Now you need to fullfill existing rows with data from subscription table
$select = $this->getConnection()->select();

$select->join(
    array('sub'=>$this->getTable('adyen_subscription/subscription')),
    'sub.order_id = order_grid.entity_id',
    array('created_subscription_id' => 'order_id')
);
$this->getConnection()->query(
    $select->crossUpdateFromSelect(
        array('order_grid' => $this->getTable('sales/order_grid'))
    )
);

$installer->endSetup();
