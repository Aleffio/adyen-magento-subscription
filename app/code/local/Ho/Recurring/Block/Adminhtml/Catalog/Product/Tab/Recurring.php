<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_Block_Adminhtml_Catalog_Product_Tab_Recurring extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('product');

        $helper = Mage::helper('ho_recurring');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('recurring_profiles_fieldset', array(
            'legend'    => $helper->__('Recurring Profile'),
        ));

        /** @var Mage_Adminhtml_Block_Widget_Button $addProfileButton */
        $addProfileButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('ho_recurring')->__('Add New Profile'),
                'onclick'   => 'addRecurringProductProfile()',
                'class'     => 'add',
            ));

        $fieldset->setHeaderBar($addProfileButton->toHtml());

        $productProfiles = Mage::getModel('ho_recurring/product_profile')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->setOrder('sort_order', Zend_Db_Select::SQL_ASC);

        $this->_renderProfileFieldset($fieldset);

        foreach ($productProfiles as $profile) {
            $this->_renderProfileFieldset($fieldset, $profile);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param Varien_Data_Form_Element_Fieldset $parentFieldset
     * @param Ho_Recurring_Model_Product_Profile $profile
     */
    protected function _renderProfileFieldset(
        Varien_Data_Form_Element_Fieldset $parentFieldset,
        Ho_Recurring_Model_Product_Profile $profile = null)
    {
        $helper = Mage::helper('ho_recurring');

        $elementId = $profile ? 'product_profile[' . $profile->getId() . ']' : 'dummy_profile[]';

        $profileFieldset = $parentFieldset->addFieldset($elementId, array(
            'class'     => 'ui-sortable-handle profile-fieldset' . (!$profile ? ' dummy-fieldset' : ''),
        ));

        $profileFieldset->addField('title-' . ($profile ? $profile->getId() : ''), 'note', array(
            'label'     => $helper->__($profile ? 'Profile:' : 'New Profile'),
            'text'      => $profile ? '<em>' . $profile->getLabel() . '</em>' : '',
        ));
        $profileFieldset->addField($elementId . '[label]', 'text', array(
            'name'      => $elementId . '[label]',
            'label'     => $helper->__('Label'),
        ))->setValue($profile ? $profile->getLabel() : '');

        $profileFieldset->addField($elementId . '[website_id]', 'select', array(
            'name'      => $elementId . '[website_id]',
            'label'     => $helper->__('Website'),
            'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(true),
        ))->setValue($profile ? $profile->getWebsiteId() : '');

        $profileFieldset->addField($elementId . '[customer_group_id]', 'select', array(
            'name'      => $elementId . '[customer_group_id]',
            'label'     => $helper->__('Customer Group'),
            'values'    => Mage::getSingleton('adminhtml/system_config_source_customer_group')->toOptionArray(),
        ))->setValue($profile ? $profile->getCustomerGroupId() : '');

        $profileFieldset->addField($elementId . '[term]', 'text', array(
            'name'      => $elementId . '[term]',
            'label'     => $helper->__('Billing Period'),
        ))->setValue($profile ? $profile->getTerm() : '');

        $profileFieldset->addField($elementId . '[term_type]', 'select', array(
            'name'      => $elementId . '[term_type]',
            'label'     => $helper->__('Billing Period Type'),
            'values'    => Mage::getSingleton('ho_recurring/system_config_source_term')->toOptionArray(true),
        ))->setValue($profile ? $profile->getTermType() : '');

        $profileFieldset->addField($elementId . '[min_billing_cycles]', 'text', array(
            'name'      => $elementId . '[min_billing_cycles]',
            'label'     => $helper->__('Min. Billing Cycles'),
        ))->setValue($profile ? $profile->getMinBillingCycles() : '');

        $profileFieldset->addField($elementId . '[max_billing_cycles]', 'text', array(
            'name'      => $elementId . '[max_billing_cycles]',
            'label'     => $helper->__('Max. Billing Cycles'),
        ))->setValue($profile ? $profile->getMaxBillingCycles() : '');

        $profileFieldset->addField($elementId . '[qty]', 'text', array(
            'name'      => $elementId . '[qty]',
            'label'     => $helper->__('Qty in Profile'),
        ))->setValue($profile ? $profile->getQty() : '');

        $profileFieldset->addField($elementId . '[price]', 'text', array(
            'name'      => $elementId . '[price]',
            'label'     => $helper->__('Price'),
        ))->setValue($profile ? $profile->getPrice() : '');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Recurring Profile');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Recurring Profile');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
