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

class Adyen_Subscription_Block_Catalog_Product_View_Subscription extends Mage_Core_Block_Template
{
    protected $_selectedOption = null;

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function isSubscriptionSelected(Adyen_Subscription_Model_Product_Subscription $subscription)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quoteItem = $quote->getItemById($this->getRequest()->getParam('id'));
        if (! $quoteItem) {
            return false;
        }

        $option = $quoteItem->getOptionByCode('additional_options');
        if (! $option) {
            return false;
        }

        $values = unserialize($option->getValue());
        foreach ($values as $value) {
            if ($value['code'] == 'adyen_subscription') {
                return $value['option_value'] == $subscription->getId();
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canOrderStandalone()
    {
        return $this->getSubscriptionType() == Adyen_Subscription_Model_Product_Subscription::TYPE_ENABLED_ALLOW_STANDALONE;
    }

    public function getJsonConfig()
    {
        $json = array();
        $json['none'] = $this->_getPriceStandaloneConfiguration();
        foreach ($this->getSubscriptionCollection() as $subscription) {
            /** @var Adyen_Subscription_Model_Product_Subscription $subscription */
            $json[$subscription->getId()] = $this->_getPriceSubscriptionConfiguration($subscription);
        }
        return json_encode($json);
    }

    protected function _getPriceStandaloneConfiguration()
    {
        $data = array();
        $data['price']      = 0;
        return $data;
    }

    /**
     * Get price configuration
     *
     * @param Adyen_Subscription_Model_Product_Subscription $subscription
     * @return array
     */
    protected function _getPriceSubscriptionConfiguration($subscription)
    {
        $data = array();
        $data['price']      = Mage::helper('core')->currency($subscription->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['oldPrice']   = Mage::helper('core')->currency($subscription->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['priceValue'] = $subscription->getPrice(false);
//        $data['type']       = $option->getPriceType();
        $data['excludeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], false);
        $data['includeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], true);
        return $data;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionType()
    {
        return $this->getProduct()->getData('adyen_subscription_type');
    }

    /**
     * @return Adyen_Subscription_Model_Resource_Product_Subscription_Collection
     */
    public function getSubscriptionCollection()
    {
        return $this->getProduct()->getData('adyen_subscription_data');
    }

    /**
     * @return Adyen_Subscription_Model_Resource_Product_Subscription_Collection
     */
    public function getOptions()
    {
        return $subscriptionCollection = Mage::getResourceModel('adyen_subscription/product_subscription_collection')
            ->addProductFilter($this->getProduct());
    }

    /**
     * @return int|null
     */
    protected function _getSelectedOption()
    {
        if (is_null($this->_selectedOption)) {
            if ($this->getProduct()->hasPreconfiguredValues()) {
                $configValue = $this->getProduct()->getPreconfiguredValues()->getData('adyen_subscription');
                if ($configValue) {
                    $this->_selectedOption = $configValue;
                }
            }
        }

        return $this->_selectedOption;
    }

    /**
     * @param int $subscriptionId
     * @return bool
     */
    protected function _isSelected($subscriptionId)
    {
        $selectedOption = $this->_getSelectedOption();

        if ($selectedOption == $subscriptionId) {
            return true;
        }

        return false;
    }
}
