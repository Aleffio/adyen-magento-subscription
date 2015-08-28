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


$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_subscription', 'is_visible', false);

$eagCollection = Mage::getResourceModel('eav/entity_attribute_group_collection');
$eagCollection->addFieldToFilter('attribute_group_name', 'Subscription');
foreach ($eagCollection as $eagItem) {
    /** @var Mage_Eav_Model_Entity_Attribute_Group $eagItem */
    $eagItem->delete();
}

$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'adyen_subscription_type', array(
    'label'                      => 'Subscription Type',
    'sort_order'                 => 10,
    'type'                       => 'int',
    'note'                       => '',
    'default'                    => null,                                                     // admin input default value
    'input'                      => 'select',                                                 // admin input type (select, text, textarea etc)
    'source'                     => 'adyen_subscription/system_config_source_subscription_type',
    'required'                   => false,                                                    // required in admin
    'user_defined'               => false,                                                    // editable in admin attributes section, false for not
    'unique'                     => false,                                                    // unique value required
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,  // (products only) scope
    'visible'                    => false,                                                    // (products only) visible on admin
    'visible_on_front'           => false,                                                    // (products only) visible on frontend (store) attribute table
    'used_in_product_listing'    => true,                                                     // (products only) made available in product listing
    'searchable'                 => false,                                                    // (products only) searchable via basic search
    'visible_in_advanced_search' => false,                                                    // (products only) searchable via advanced search
    'filterable'                 => false,                                                    // (products only) use in layered nav
    'filterable_in_search'       => false,                                                    // (products only) use in search results layered nav
    'comparable'                 => false,                                                    // (products only) comparable on frontend
    'is_html_allowed_on_front'   => false,                                                    // (products only) seems obvious, but also see visible
    'apply_to'                   => 'simple',                                                 // (products only) which product types to apply to
    'is_configurable'            => false,                                                    // (products only) used for configurable products or not
    'used_for_sort_by'           => false,                                                    // (products only) available in the 'sort by' menu
    'position'                   => 0,                                                        // (products only) position in layered navigation
    'used_for_promo_rules'       => false,                                                    // (products only) available for use in promo rules
));

$installer->endSetup();
