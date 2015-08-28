<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Catalog_Product_Tab_Subscription extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $product = $this->_getProduct();

        $helper = Mage::helper('adyen_subscription');

        $form = new Varien_Data_Form();

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $form->addFieldset('subscription_fieldset', array(
            'legend'    => $helper->__('Adyen Subscription'),
        ));

        /** @var Mage_Adminhtml_Block_Widget_Button $addSubscriptionButton */
        $addSubscriptionButton = $this->getLayout()->createBlock('adminhtml/widget_button')->setData([
            'label'        => Mage::helper('adyen_subscription')->__('Add New Adyen Subscription'),
            'class'        => 'add product-subscription-add',
            'element_name' => 'product_subscription_add',
        ]);

        $fieldset->setHeaderBar($addSubscriptionButton->toHtml());

        $productSubscriptions = Mage::getModel('adyen_subscription/product_subscription')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId())
            ->setOrder('sort_order', Zend_Db_Select::SQL_ASC);

        $subscriptionAttribute = $product->getAttributes()['adyen_subscription_type'];
        $subscriptionAttribute->setIsVisible(1);
        $this->_setFieldset([$subscriptionAttribute], $fieldset);
        $adyenSubscriptionType = $form->getElement('adyen_subscription_type');
        $adyenSubscriptionType->setName('product[adyen_subscription_type]');
        $adyenSubscriptionType->setValue($product->getData('adyen_subscription_type'));
        $adyenSubscriptionType->setNote(
            $helper->__('%s to add a new subscription.', '<i>'.$helper->__('Add New Adyen Subscription').'</i>')."<br />\n".
            $helper->__('Drag and drop to reorder')
        );

        $this->_renderSubscriptionFieldset($fieldset);
        foreach ($productSubscriptions as $subscription) {
            $this->_renderSubscriptionFieldset($fieldset, $subscription);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }


    /**
     * @param Varien_Data_Form_Element_Fieldset  $parentFieldset
     * @param Adyen_Subscription_Model_Product_Subscription $subscription
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    protected function _renderSubscriptionFieldset(
        Varien_Data_Form_Element_Fieldset $parentFieldset,
        Adyen_Subscription_Model_Product_Subscription $subscription = null)
    {
        $helper = Mage::helper('adyen_subscription');
        $subscriptionCount = 0;

        // retrieve
        $linkedSubscriptionText = "";
        if($subscription) {

            $productSubscriptionCollection = Mage::getModel('adyen_subscription/subscription_item')
                ->getCollection();

            $productSubscriptionCollection->addFieldToFilter('product_subscription_id', $subscription->getId());
            $resource = $productSubscriptionCollection->getResource();

            $productSubscriptionCollection->getSelect()->joinLeft(
                array('subscription' => $resource->getTable('adyen_subscription/subscription')),
                'main_table.subscription_id = subscription.entity_id'
            );
            $productSubscriptionCollection->getSelect()->where('main_table.status = ?', Adyen_Subscription_Model_Subscription::STATUS_ACTIVE);
            $productSubscriptionCollection->getSelect()->group('subscription_id');

            $subscriptionCount = $productSubscriptionCollection->count();
            if ($subscriptionCount > 0) {
                $linkedSubscriptionText = "<br />" . $helper->__(" Currently used in %s active subscription(s)", $subscriptionCount);
                // add extra classname to indicate there are linked subscription to this productSubscription on removal
            } else {
                $linkedSubscriptionText = "<br />" . $helper->__(" Currently not used in any active subscription");
            }
        }

        $elementId = $subscription ? 'product_subscription[' . $subscription->getId() . ']' : 'product_subscription[template]';

        $subscriptionFieldset = $parentFieldset->addFieldset($elementId, array(
            'legend'    => $subscription
                    ? $helper->__('Subscription: %s (ID: %s)', '<em>' . $subscription->getLabel() . '</em>', $subscription->getId())
                    : $helper->__('New Adyen Subscription')
                    . $linkedSubscriptionText,
            'class'     => 'subscription-fieldset' . (!$subscription ? ' product-fieldset-template' : ''),
            'name'      => $elementId . '[fieldset]'
        ))->setRenderer(
            $this->getLayout()->createBlock('adyen_subscription/adminhtml_catalog_product_tab_subscription_fieldset')
        );
        $subscriptionFieldset->addType(
            'price',
            Mage::getConfig()->getBlockClassName('adyen_subscription/adminhtml_catalog_product_tab_subscription_price')
        );

        $data = array(  'label'   => 'Delete Subscription',
                        'class'   => 'delete product-subscription-delete');

        if ($subscriptionCount > 0) {
            $data['onclick'] = 'var message = \'' . $helper->__('There are subscriptions using this method are you sure you want to delete it? it will not change the current subscriptions.') . '\'; if( confirm(message) ) { $(this).up(\'.subscription-fieldset-container\').remove(); }';
        } else {
            $data['onclick'] = '$(this).up(\'.subscription-fieldset-container\').remove();';
        }

        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData($data);

        $button->setName('delete_subscription');
        $subscriptionFieldset->setHeaderBar($button->toHtml());

        $inStore = Mage::app()->getRequest()->getParam('store');

        $subscriptionFieldset->addField($elementId . '[label]', 'text', array(
            'name'      => $elementId . '[label]',
            'label'     => $helper->__('Label'),
            'disabled'  => $inStore && ($subscription ? !$subscription->getStoreLabel($inStore) : false), // @todo won't disable
            'required'  => true,
            'after_element_html' => $inStore ? '</td><td class="use-default">
            <input id="' . $elementId . '[use_default]" name="' . $elementId . '[use_default]" type="checkbox" value="1" class="checkbox config-inherit" '
                . (($subscription ? $subscription->getStoreLabel($inStore) : false) ? '' : 'checked="checked"') . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" />
            <label for="' . $elementId . '[use_default]" class="inherit">' . Mage::helper('adyen_subscription')->__('Use Default') . '</label>
          </td><td class="scope-label">
            [' . $helper->__('STORE VIEW') . ']
          ' : '</td><td class="scope-label">
            [' . $helper->__('STORE VIEW') . ']',
        ))->setValue($subscription ? $subscription->getLabel($inStore) : '');

        $subscriptionFieldset->addField($elementId . '[website_id]', 'select', array(
            'name'      => $elementId . '[website_id]',
            'label'     => $helper->__('Website'),
            'values'    => Mage::getSingleton('adyen_subscription/system_config_source_subscription_websites')->toOptionArray(),
        ))->setValue($subscription ? $subscription->getWebsiteId() : '');

        $subscriptionFieldset->addField($elementId . '[customer_group_id]', 'select', array(
            'name'      => $elementId . '[customer_group_id]',
            'label'     => $helper->__('Customer Group'),
            'values'    => Mage::getSingleton('adyen_subscription/system_config_source_subscription_groups')->toOptionArray(),
        ))->setValue($subscription ? $subscription->getCustomerGroupId() : '');

        $subscriptionFieldset->addField($elementId . '[term]', 'text', array(
            'name'      => $elementId . '[term]',
            'required'  => true,
            'class' => 'validate-digits validate-digits-range digits-range-1-3153600000',
            'label'     => $helper->__('Billing Period'),
        ))->setValue($subscription ? $subscription->getTerm() : '');

        $subscriptionFieldset->addField($elementId . '[term_type]', 'select', array(
            'name'      => $elementId . '[term_type]',
            'label'     => $helper->__('Billing Period Unit'),
            'required'  => true,
            'values'    => Mage::getSingleton('adyen_subscription/system_config_source_term')->toOptionArray(true),
            'note'      => $this->__('Subscription will be created every [Billing Period] [Billing Period Unit], e.g. every 3 months.')
        ))->setValue($subscription ? $subscription->getTermType() : '');

        // Min and max billing cycle currently not in use
//        $subscriptionFieldset->addField($elementId . '[min_billing_cycles]', 'text', array(
//            'name'      => $elementId . '[min_billing_cycles]',
//            'required'  => true,
//            'class'     => 'validate-digits validate-digits-range digits-range-1-3153600000',
//            'label'     => $helper->__('Min. Billing Cycles'),
//        ))->setValue($subscription ? $subscription->getMinBillingCycles() : '1');
//
//        $subscriptionFieldset->addField($elementId . '[max_billing_cycles]', 'text', array(
//            'name'      => $elementId . '[max_billing_cycles]',
//            'label'     => $helper->__('Max. Billing Cycles'),
//        ))->setValue($subscription ? $subscription->getMaxBillingCycles() : '');

        $subscriptionFieldset->addField($elementId . '[qty]', 'text', array(
            'name'      => $elementId . '[qty]',
            'required'  => true,
            'class'     => 'validate-number',
            'label'     => $helper->__('Qty in Subscription'),
        ))->setValue($subscription ? $subscription->getQty() * 1 : '1');

        /** @var Adyen_Subscription_Block_Adminhtml_Catalog_Product_Tab_Subscription_Price $priceField */
        $priceField = $subscriptionFieldset->addField($elementId . '[price]', 'price', array(
            'name'      => $elementId . '[price]',
            'label'     => $helper->__('Price'),
            'class'     => 'price-tax-calc',
            'identifier' => $subscription ? $subscription->getId() : 'template',
            'subscription_count' => $subscription ? $this->_getSubscriptionUsedCount($subscription) : 0
        ));
        $priceField->setValue($subscription ? $subscription->getPrice() * 1 : '');
        $priceField->setSubscription($subscription);

        $subscriptionFieldset->addField($elementId . '[show_on_frontend]', 'select', array(
            'name'      => $elementId . '[show_on_frontend]',
            'label'     => $helper->__('Show on Frontend'),
            'options'   => array(1 => $helper->__('Yes'), 0 => $helper->__('No')),
        ))->setValue($subscription ? $subscription->getShowOnFrontend() : 0);

        if ($subscriptionCount > 0) {
            $subscriptionFieldset->addField($elementId . '[warning]', 'note', array(
                'text'     => '<p style="display:none;" class=\'adyen_notice notice\'>' . $helper->__('Watch out! this product subscription is used in current subscriptions. Change this will not change the current subscriptions') . '</p>'
            ));
        }

        return $subscriptionFieldset;
    }


    /**
     * @param Adyen_Subscription_Model_Product_Subscription $subscription
     *
     * @return int
     */
    protected function _getSubscriptionUsedCount(Adyen_Subscription_Model_Product_Subscription $subscription)
    {
        return 0;
        Mage::getResourceModel('adyen_subscription/subscription_item_collection')
            ->addFieldToFilter('subscription_product_id', $subscription->getId());
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
        return $this->__('Adyen Subscription');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Adyen Subscription');
    }

    /**
     * Only show when adyen_subscription_type attribute exists
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

        return array_key_exists('adyen_subscription_type', Mage::registry('product')->getAttributes());
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
