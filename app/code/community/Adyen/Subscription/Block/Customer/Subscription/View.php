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

class Adyen_Subscription_Block_Customer_Subscription_View extends Mage_Core_Block_Template
{
    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        $subscription = Mage::registry('adyen_subscription');

        return $subscription;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adyen_subscription/customer/subscriptions');
    }

    public function getCanCancel()
    {
        $subscription = $this->getSubscription();
        if($subscription->getStatus() == Adyen_Subscription_Model_Subscription_Item::STATUS_ACTIVE) {
            return Mage::getStoreConfigFlag(
                'adyen_subscription/subscription/allow_cancel_subscription',
                Mage::app()->getStore()
            );
        }
        return false;
    }

    /**
     * Set data to block
     *
     * @return string
     */
    protected function _toHtml()
    {

        if($this->getSubscription()) {
            $this->setCancelUrl(
                $this->getUrl('adyen_subscription/customer/cancel', array(
                    '_current' => true))
            );
        }


        return parent::_toHtml();
    }

}
