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
 * Class Adyen_Subscription_Model_Profile_Order
 *
 * @method int getProfileId()
 * @method Adyen_Subscription_Model_Profile_Order setProfileId(int $value)
 * @method int getEntityId()
 * @method Adyen_Subscription_Model_Profile_Order setEntityId(int $value)
 * @method int getOrderId()
 * @method Adyen_Subscription_Model_Profile_Order setOrderId(int $value)
 */
class Adyen_Subscription_Model_Profile_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/profile_order');
    }

    /**
     * @param Adyen_Subscription_Model_Profile $profile
     * @return Adyen_Subscription_Model_Profile_Order
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
            // Note: The order won't load if we don't set the store ID
            $order = Mage::getModel('adyen_subscription/profile')
                ->load($this->getProfileId());

            $this->setData('_profile', $order);
        }

        return $this->getData('_profile');
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
