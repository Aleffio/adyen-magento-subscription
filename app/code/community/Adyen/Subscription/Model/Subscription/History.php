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
 * Class Adyen_Subscription_Model_Subscription_Order
 *
 * @method int getEntityId()
 * @method Adyen_Subscription_Model_Subscription_Order setEntityId(int $value)
 * @method int getSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Item setSubscriptionId(int $value)
 * @method int getUserId()
 * @method Adyen_Subscription_Model_Subscription_Item setUserId(int $value)
 * @method int getCustomerId()
 * @method Adyen_Subscription_Model_Subscription_Item setCustomerId(int $value)
 * @method string getStatus()
 * @method Adyen_Subscription_Model_Subscription_Item setStatus(string $value)
 * @method string getCode()
 * @method Adyen_Subscription_Model_Subscription_Item setCode(string $value)
 * @method string getDate()
 * @method Adyen_Subscription_Model_Subscription_Item setDate(string $value)
 *
 *
 */
class Adyen_Subscription_Model_Subscription_History extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('adyen_subscription/subscription_history');
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return Adyen_Subscription_Model_Subscription_History
     */
    public function setSubscription(Adyen_Subscription_Model_Subscription $subscription)
    {
        $this->setSubscriptionId($subscription->getId());
        return $this;
    }

    /**
     * Save object data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        $this->setDate(now());
        parent::save();
    }

    public function createHistoryFromSubscription(Adyen_Subscription_Model_Subscription $subscription)
    {
        return $this->saveFromSubscription($subscription, true);
    }

    public function saveFromSubscription(Adyen_Subscription_Model_Subscription $subscription, $save = true)
    {
        $this->setSubscription($subscription);
        // check if user is frontend or admin user
        if(Mage::app()->getStore()->isAdmin())
        {
            $user = Mage::getSingleton('admin/session');
            if($user->getUser()) {
                $userid = $user->getUser()->getUserId();
                $this->setUserId($userid);
            }
        } else {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            $this->setCustomerId($customerId);
        }
        $this->setStatus($subscription->getStatus());

        if($subscription->getStatus() == Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR) {
            $this->setCode($subscription->getErrorMessage());
        } else {
            $this->setCode($subscription->getCancelCode());
        }

        if($save) {
            $this->save();
        } else {
            return $this;
        }
    }
}