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
        if($subscription->getStatus() == Adyen_Subscription_Model_Subscription::STATUS_ACTIVE) {
            return Mage::getStoreConfigFlag(
                'adyen_subscription/subscription/allow_cancel_subscription',
                Mage::app()->getStore()
            );
        }
        return false;
    }

    public function getCanPause()
    {
        $subscription = $this->getSubscription();
        if($subscription->getStatus() == Adyen_Subscription_Model_Subscription::STATUS_ACTIVE) {
            return Mage::getStoreConfigFlag(
                'adyen_subscription/subscription/allow_pause_resume_subscription',
                Mage::app()->getStore()
            );
        }
        return false;
    }

    public function getCanResume()
    {
        $subscription = $this->getSubscription();
        if($subscription->getStatus() == Adyen_Subscription_Model_Subscription::STATUS_PAUSED) {
            return Mage::getStoreConfigFlag(
                'adyen_subscription/subscription/allow_pause_resume_subscription',
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
            $this->setPauseUrl(
                $this->getUrl('adyen_subscription/customer/pause', array(
                    '_current' => true))
            );
            $this->setResumeUrl(
                $this->getUrl('adyen_subscription/customer/resume', array(
                    '_current' => true))
            );
        }


        return parent::_toHtml();
    }

}
