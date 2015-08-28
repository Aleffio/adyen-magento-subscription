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

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();


$subscriptionTable = $installer->getTable('adyen_subscription/product_subscription');
$connection->addColumn($subscriptionTable, 'update_price', [
    'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'nullable'  => false,
    'default'   => 0,
    'comment'   => 'Update Price',
    'after'     => 'price',
]);


$subscriptionItemTable = $installer->getTable('adyen_subscription/subscription_item');
$productSubscriptionTable = $installer->getTable('adyen_subscription/product_subscription');

$connection->addColumn($subscriptionItemTable, 'product_subscription_id', [
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'length'    => 11,
    'nullable'  => false,
    'default'   => 0,
    'comment'   => 'Product Subscription Id',
    'after'     => 'subscription_id',
]);

$connection->addForeignKey(
    $installer->getFkName($subscriptionItemTable, 'product_subscription_id', $productSubscriptionTable, 'entity_id'),
    $subscriptionItemTable, 'product_subscription_id', $productSubscriptionTable, 'entity_id'
);

$items = Mage::getResourceModel('adyen_subscription/subscription_item_collection');
foreach ($items as $item) {
    /** @var Adyen_Subscription_Model_Subscription_Item $item */
    $productOptions = $item->getProductOptions();

    $productSubscriptionId =
        isset($productOptions['info_buyRequest']['ho_recurring_profile'])
            ? $productOptions['info_buyRequest']['ho_recurring_profile']
            : (isset($productOptions['info_buyRequest']['adyen_subscription'])
                ? $productOptions['info_buyRequest']['adyen_subscription']
                : null);

    $item->setProductSubscriptionId($productSubscriptionId);
    $item->save();
}

$installer->endSetup();
