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
