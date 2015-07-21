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

class Adyen_Subscription_Block_Catalog_Product_View_Profile extends Mage_Core_Block_Template
{
    protected $_selectedOption = null;

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    public function isProfileSelected(Adyen_Subscription_Model_Product_Profile $profile)
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
            if ($value['code'] == 'adyen_subscription_profile') {
                return $value['option_value'] == $profile->getId();
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canOrderStandalone()
    {
        return $this->getProfileType() == Adyen_Subscription_Model_Product_Profile::TYPE_ENABLED_ALLOW_STANDALONE;
    }

    public function getJsonConfig()
    {
        $json = [];
        $json['none'] = $this->_getPriceStandaloneConfiguration();
        foreach ($this->getProfileCollection() as $profile) {
            /** @var Adyen_Subscription_Model_Product_Profile $profile */
            $json[$profile->getId()] = $this->_getPriceProfileConfiguration($profile);
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
     * @param Adyen_Subscription_Model_Product_Profile $profile
     * @return array
     */
    protected function _getPriceProfileConfiguration($profile)
    {
        $data = array();
        $data['price']      = Mage::helper('core')->currency($profile->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['oldPrice']   = Mage::helper('core')->currency($profile->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['priceValue'] = $profile->getPrice(false);
//        $data['type']       = $option->getPriceType();
        $data['excludeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], false);
        $data['includeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], true);
        return $data;
    }

    /**
     * @return mixed
     */
    public function getProfileType()
    {
        return $this->getProduct()->getData('adyen_subscription_type');
    }

    /**
     * @return Adyen_Subscription_Model_Resource_Product_Profile_Collection
     */
    public function getProfileCollection()
    {
        return $this->getProduct()->getData('adyen_subscription_data');
    }

    /**
     * @return Adyen_Subscription_Model_Resource_Product_Profile_Collection
     */
    public function getOptions()
    {
        return $profileCollection = Mage::getResourceModel('adyen_subscription/product_profile_collection')
            ->addProductFilter($this->getProduct());
    }

    /**
     * @return int|null
     */
    protected function _getSelectedOption()
    {
        if (is_null($this->_selectedOption)) {
            if ($this->getProduct()->hasPreconfiguredValues()) {
                $configValue = $this->getProduct()->getPreconfiguredValues()->getData('adyen_subscription_profile');
                if ($configValue) {
                    $this->_selectedOption = $configValue;
                }
            }
        }

        return $this->_selectedOption;
    }

    /**
     * @param int $profileId
     * @return bool
     */
    protected function _isSelected($profileId)
    {
        $selectedOption = $this->_getSelectedOption();

        if ($selectedOption == $profileId) {
            return true;
        }

        return false;
    }
}
