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
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

/**
 * Add a preorder attribute for products that can be ordered in advance (later shipment date)
 */
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$installer->startSetup();

$installer->addAttribute('catalog_product', 'can_preorder', array(
    'group'                     => 'General',
    'input'                     => 'select',
    'type'                      => 'int',
    'label'                     => 'Can preorder',
    'source'                    => 'eav/entity_attribute_source_boolean',
    'global'                    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                   => 1,
    'required'                  => 0,
    'visible_on_front'          => 0,
    'is_html_allowed_on_front'  => 0,
    'is_configurable'           => 0,
    'searchable'                => 0,
    'filterable'                => 0,
    'comparable'                => 0,
    'unique'                    => false,
    'user_defined'              => false,
    'default'                   => 0,
    'is_user_defined'           => false,
    'used_in_product_listing'   => true
));

$installer->endSetup();


/**
 * Add the order shipment date to the sales tables
 */
/* @var $installer Mage_Sales_Model_Resource_Setup */
$installer = Mage::getResourceModel('sales/setup', 'core_setup');

$installer->startSetup();

$installer->addAttribute('order', 'scheduled_at', ['type' => 'datetime', 'grid' => true]);
$installer->addAttribute('quote', 'scheduled_at', ['type' => 'datetime', 'grid' => true]);

if (Mage::getEdition() == Mage::EDITION_ENTERPRISE) {
    $installer->getConnection()
        ->addColumn($installer->getTable('enterprise_salesarchive/order_grid'), 'scheduled_at', array(
            'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
            'comment' => 'Order schedule date',
        ));
}

$installer->endSetup();
