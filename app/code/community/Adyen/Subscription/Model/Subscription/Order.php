<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

/**
 * Class Adyen_Subscription_Model_Subscription_Order
 *
 * @method int getSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Order setSubscriptionId(int $value)
 * @method int getEntityId()
 * @method Adyen_Subscription_Model_Subscription_Order setEntityId(int $value)
 * @method int getOrderId()
 * @method Adyen_Subscription_Model_Subscription_Order setOrderId(int $value)
 */
class Adyen_Subscription_Model_Subscription_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/subscription_order');
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return Adyen_Subscription_Model_Subscription_Order
     */
    public function setSubscription(Adyen_Subscription_Model_Subscription $subscription)
    {
        $this->setData('_subscription', $subscription);
        $this->setSubscriptionId($subscription->getId());
        return $this;
    }


    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if (! $this->hasData('_subscription')) {
            // Note: The order won't load if we don't set the store ID
            $order = Mage::getModel('adyen_subscription/subscription')
                ->load($this->getSubscriptionId());

            $this->setData('_subscription', $order);
        }

        return $this->getData('_subscription');
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @return Adyen_Subscription_Model_Subscription_Order
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
                ->setStoreId($this->getSubscription()->getStoreId())
                ->load($this->getOrderId());

            $this->setData('_order', $order);
        }

        return $this->getData('_order');
    }
}
