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
        $product = $this->_getProduct();

        $helper = Mage::helper('ho_recurring');

        $form = new Varien_Data_Form();

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->addFieldset('recurring_profiles_fieldset', array(
            'legend'    => $helper->__('Recurring Profile'),
        ));

        /** @var Mage_Adminhtml_Block_Widget_Button $addProfileButton */
        $addProfileButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData([
            'label'        => Mage::helper('ho_recurring')->__('Add New Profile'),
            'class'        => 'add product-profile-add',
            'element_name' => 'product_profile_add',
        ]);

        $fieldset->setHeaderBar($addProfileButton->toHtml());

        $productProfiles = Mage::getModel('ho_recurring/product_profile')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->setOrder('sort_order', Zend_Db_Select::SQL_ASC);

        $recurringAttribute = $product->getAttributes()['ho_recurring_type'];
        $recurringAttribute->setIsVisible(1);
        $this->_setFieldset([$recurringAttribute], $fieldset);
        $hoRecurringType = $form->getElement('ho_recurring_type');
        $hoRecurringType->setName('product[ho_recurring_type]');
        $hoRecurringType->setValue($product->getData('ho_recurring_type'));
        $hoRecurringType->setNote(
            $helper->__('%s to add a new profile.', '<i>'.$helper->__('Add New Profile').'</i>')."<br />\n".
            $helper->__('Drag and drop to reorder')
        );

        $this->_renderProfileFieldset($fieldset);
        foreach ($productProfiles as $profile) {
            $this->_renderProfileFieldset($fieldset, $profile);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * @param Varien_Data_Form_Element_Fieldset  $parentFieldset
     * @param Ho_Recurring_Model_Product_Profile $profile
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    protected function _renderProfileFieldset(
        Varien_Data_Form_Element_Fieldset $parentFieldset,
        Ho_Recurring_Model_Product_Profile $profile = null)
    {
        $helper = Mage::helper('ho_recurring');

        $elementId = $profile ? 'product_profile[' . $profile->getId() . ']' : 'product_profile[template]';

        $profileFieldset = $parentFieldset->addFieldset($elementId, array(
            'legend'    => $helper->__($profile ? 'Profile: <em>' . $profile->getLabel() . '</em>' : 'New Profile'),
            'class'     => 'profile-fieldset' . (!$profile ? ' product-fieldset-template' : ''),
            'name'      => $elementId . '[fieldset]'
        ))->setRenderer(
            $this->getLayout()->createBlock('ho_recurring/adminhtml_catalog_product_tab_recurring_fieldset')
        );
        $profileFieldset->addType(
            'price',
            Mage::getConfig()->getBlockClassName('ho_recurring/adminhtml_catalog_product_tab_recurring_price')
        );

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => 'Delete Profile',
                'onclick' => 'return false;',
                'class'   => 'delete product-profile-delete',
            ));
        $button->setName('delete_profile');
        $profileFieldset->setHeaderBar($button->toHtml());

        $inStore = Mage::app()->getRequest()->getParam('store');

        $profileFieldset->addField($elementId . '[label]', 'text', array(
            'name'      => $elementId . '[label]',
            'label'     => $helper->__('Label'),
            'disabled'  => $inStore && ($profile ? !$profile->getStoreLabel($inStore) : false), // @todo won't disable
            'required'  => true,
            'after_element_html' => $inStore ? '</td><td class="use-default">
            <input id="' . $elementId . '[use_default]" name="' . $elementId . '[use_default]" type="checkbox" value="1" class="checkbox config-inherit" '
                . (($profile ? $profile->getStoreLabel($inStore) : false) ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
            <label for="' . $elementId . '[use_default]" class="inherit">' . Mage::helper('ho_recurring')->__('Use Default') . '</label>
          </td><td class="scope-label">
            [' . $helper->__('STORE VIEW') . ']
          ' : '</td><td class="scope-label">
            [' . $helper->__('STORE VIEW') . ']',
        ))->setValue($profile ? $profile->getLabel($inStore) : '');

        $profileFieldset->addField($elementId . '[website_id]', 'select', array(
            'name'      => $elementId . '[website_id]',
            'label'     => $helper->__('Website'),
            'values'    => Mage::getSingleton('ho_recurring/system_config_source_profile_websites')->toOptionArray(),
        ))->setValue($profile ? $profile->getWebsiteId() : '');

        $profileFieldset->addField($elementId . '[customer_group_id]', 'select', array(
            'name'      => $elementId . '[customer_group_id]',
            'label'     => $helper->__('Customer Group'),
            'values'    => Mage::getSingleton('ho_recurring/system_config_source_profile_groups')->toOptionArray(),
        ))->setValue($profile ? $profile->getCustomerGroupId() : '');

        $profileFieldset->addField($elementId . '[term]', 'text', array(
            'name'      => $elementId . '[term]',
            'required'  => true,
            'class' => 'validate-digits validate-digits-range digits-range-1-3153600000',
            'label'     => $helper->__('Billing Frequency'),
        ))->setValue($profile ? $profile->getTerm() : '');

        $profileFieldset->addField($elementId . '[term_type]', 'select', array(
            'name'      => $elementId . '[term_type]',
            'label'     => $helper->__('Billing Period Unit'),
            'required'  => true,
            'values'    => Mage::getSingleton('ho_recurring/system_config_source_term')->toOptionArray(true),
        ))->setValue($profile ? $profile->getTermType() : '');

        // Min and max billing cycle currently not in use
//        $profileFieldset->addField($elementId . '[min_billing_cycles]', 'text', array(
//            'name'      => $elementId . '[min_billing_cycles]',
//            'required'  => true,
//            'class'     => 'validate-digits validate-digits-range digits-range-1-3153600000',
//            'label'     => $helper->__('Min. Billing Cycles'),
//        ))->setValue($profile ? $profile->getMinBillingCycles() : '1');
//
//        $profileFieldset->addField($elementId . '[max_billing_cycles]', 'text', array(
//            'name'      => $elementId . '[max_billing_cycles]',
//            'label'     => $helper->__('Max. Billing Cycles'),
//        ))->setValue($profile ? $profile->getMaxBillingCycles() : '');

        $profileFieldset->addField($elementId . '[qty]', 'text', array(
            'name'      => $elementId . '[qty]',
            'required'  => true,
            'class'     => 'validate-number',
            'label'     => $helper->__('Qty in Profile'),
        ))->setValue($profile ? $profile->getQty() * 1 : '1');

        /** @var Ho_Recurring_Block_Adminhtml_Catalog_Product_Tab_Recurring_Price $priceField */
        $priceField = $profileFieldset->addField($elementId . '[price]', 'price', array(
            'name'      => $elementId . '[price]',
            'label'     => $helper->__('Price'),
            'class'     => 'price-tax-calc',
            'identifier' => $profile ? $profile->getId() : 'template'
        ));
        $priceField->setValue($profile ? $profile->getPrice() * 1 : '');
        $priceField->setProfile($profile);

        $profileFieldset->addField($elementId . '[show_on_frontend]', 'select', array(
            'name'      => $elementId . '[show_on_frontend]',
            'label'     => $helper->__('Show on Frontend'),
            'options'   => array(1 => $helper->__('Yes'), 0 => $helper->__('No')),
        ))->setValue($profile ? $profile->getShowOnFrontend() : 0);

        return $profileFieldset;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('product');
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
     * Only show when ho_recurring_type attribute exists
     * and in case of a bundle, the price type must be fixed
     *
     * @return bool
     */
    public function canShowTab()
    {
        $product = $this->_getProduct();

        if ($product->getTypeId() == 'bundle'
            && $product->getPriceType() != Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
            return false;
        }

        return array_key_exists('ho_recurring_type', Mage::registry('product')->getAttributes());
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
