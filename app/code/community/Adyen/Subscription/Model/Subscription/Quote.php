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
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

/**
 * Class Adyen_Subscription_Model_Subscription_Quote
 *
 * @method int getSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Quote setSubscriptionId(int $value)
 * @method int getQuoteId()
 * @method Adyen_Subscription_Model_Subscription_Quote setQuoteId(int $value)
 * @method int getEntityId()
 * @method Adyen_Subscription_Model_Subscription_Quote setEntityId(int $value)
 * @method int getOrderId()
 * @method Adyen_Subscription_Model_Subscription_Quote setOrderId(int $value)
 */
class Adyen_Subscription_Model_Subscription_Quote extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/subscription_quote');
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return Adyen_Subscription_Model_Subscription_Quote
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
            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('adyen_subscription/subscription')
                ->load($this->getSubscriptionId());

            $this->setData('_subscription', $quote);
        }

        return $this->getData('_subscription');
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Adyen_Subscription_Model_Subscription_Quote
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
                ->setStoreId($this->getSubscription()->getStoreId())
                ->load($this->getQuoteId());

            $this->setData('_quote', $quote);
        }

        return $this->getData('_quote');
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
