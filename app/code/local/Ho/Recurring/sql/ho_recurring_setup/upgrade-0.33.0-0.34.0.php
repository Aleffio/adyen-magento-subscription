<?php

/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var Magento_Db_Adapter_Pdo_Mysql $connection */
$connection = $installer->getConnection();

$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'ho_recurring_type', 'filterable', true);
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'ho_recurring_type', 'used_in_product_listing', true);
$installer->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'ho_recurring_type', 'used_for_sort_by', true);

$installer->endSetup();
