<?php

$installer = new Mage_Customer_Model_Entity_Setup('core_setup');

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('customer', 'adyen_can_recur',  array(
    'type'          => 'int',
    'backend'       => '',
    'label'         => 'Adyen Subscriptions are allowed',
    'input'         => 'boolean',
    'source'        => '',
    'visible'       => 1,
    'required'      => 0,
    'default'       => '1',
    'frontend'      => '',
    'unique'        => 0,
    'note'          => 'if set to \'no\' no orders will be created for this users subscriptions',
    'user_defined'  => 0,
    'sort_order'    => 999, //sort_order in forms
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'adyen_can_recur',
    '999'  //sort_order
);

$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'adyen_can_recur');
$attribute->setData('used_in_forms', array('adminhtml_customer'));
$attribute->save();