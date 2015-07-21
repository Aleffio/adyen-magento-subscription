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

/**
 * Class Adyen_Subscription_Model_Profile_Quote
 *
 * @method int getProfileId()
 * @method Adyen_Subscription_Model_Profile_Quote setProfileId(int $value)
 * @method int getQuoteId()
 * @method Adyen_Subscription_Model_Profile_Quote setQuoteId(int $value)
 * @method int getEntityId()
 * @method Adyen_Subscription_Model_Profile_Quote setEntityId(int $value)
 * @method int getOrderId()
 * @method Adyen_Subscription_Model_Profile_Quote setOrderId(int $value)
 */
class Adyen_Subscription_Model_Profile_Quote extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/profile_quote');
    }

    /**
     * @param Adyen_Subscription_Model_Profile $profile
     * @return Adyen_Subscription_Model_Profile_Quote
     */
    public function setProfile(Adyen_Subscription_Model_Profile $profile)
    {
        $this->setData('_profile', $profile);
        $this->setProfileId($profile->getId());
        return $this;
    }


    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        if (! $this->hasData('_profile')) {
            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('adyen_subscription/profile')
                ->load($this->getProfileId());

            $this->setData('_profile', $quote);
        }

        return $this->getData('_profile');
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Adyen_Subscription_Model_Profile_Quote
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->setData('_quote', $quote);
        $this->setQuoteId($quote->getId());
        return $this;
    }


    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (! $this->hasData('_quote')) {
            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('sales/quote')
                ->setStoreId($this->getProfile()->getStoreId())
                ->load($this->getQuoteId());

            $this->setData('_quote', $quote);
        }

        return $this->getData('_quote');
    }



    /**
     * @param Mage_Sales_Model_Order $order
     * @return Adyen_Subscription_Model_Profile_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->setData('_order', $order);
        $this->setOrderId($order->getId());
        return $this;
    }


    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (! $this->hasData('_order')) {
            // Note: The order won't load if we don't set the store ID
            $order = Mage::getModel('sales/order')
                ->setStoreId($this->getProfile()->getStoreId())
                ->load($this->getOrderId());

            $this->setData('_order', $order);
        }

        return $this->getData('_order');
    }
}
