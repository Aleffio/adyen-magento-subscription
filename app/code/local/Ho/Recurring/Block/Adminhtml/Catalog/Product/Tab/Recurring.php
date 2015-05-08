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
    protected function _prepareForm()
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('product');

        $helper = Mage::helper('ho_recurring');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => $helper->__('Recurring Profile'),
        ));

        $productProfiles = Mage::getModel('ho_recurring/product_profile')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->setOrder('sort_order', Zend_Db_Select::SQL_ASC);

        foreach ($productProfiles as $profile) {
            $profileFieldset = $fieldset->addFieldset('profile[' . $profile->getId() . ']', array(
                'legend'    => $helper->__('Profile'),
            ));

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][label]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][label]',
                'label'     => $helper->__('Label'),
                'required'  => true,
            ))->setValue($profile->getLabel());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][website_id]', 'select', array(
                'name'      => 'product_profile[' . $profile->getId() . '][website_id]',
                'label'     => $helper->__('Website'),
                'values'    => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(true),
            ))->setValue($profile->getWebsiteId());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][customer_group_id]', 'select', array(
                'name'      => 'product_profile[' . $profile->getId() . '][customer_group_id]',
                'label'     => $helper->__('Customer Group'),
                'values'    => Mage::getSingleton('adminhtml/system_config_source_customer_group')->toOptionArray(),
            ))->setValue($profile->getCustomerGroupId());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][term]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][term]',
                'label'     => $helper->__('Billing Period'),
            ))->setValue($profile->getTerm());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][term_type]', 'select', array(
                'name'      => 'product_profile[' . $profile->getId() . '][term_type]',
                'label'     => $helper->__('Billing Period Type'),
                'values'    => Mage::getSingleton('ho_recurring/system_config_source_term')->toOptionArray(true),
            ))->setValue($profile->getTermType());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][min_billing_cycles]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][min_billing_cycles]',
                'label'     => $helper->__('Min. Billing Cycles'),
            ))->setValue($profile->getMinBillingCycles());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][max_billing_cycles]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][max_billing_cycles]',
                'label'     => $helper->__('Max. Billing Cycles'),
            ))->setValue($profile->getMaxBillingCycles());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][qty]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][qty]',
                'label'     => $helper->__('Qty in Profile'),
            ))->setValue($profile->getQty());

            $profileFieldset->addField('product_profile[' . $profile->getId() . '][price]', 'text', array(
                'name'      => 'product_profile[' . $profile->getId() . '][price]',
                'label'     => $helper->__('Price'),
            ))->setValue($profile->getPrice());
        }

        $this->setForm($form);

        return parent::_prepareForm();
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
