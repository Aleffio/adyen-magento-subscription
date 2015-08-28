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

class Adyen_Subscription_Block_Customer_Subscription_List extends Mage_Core_Block_Template
{
    /**
     * @return Adyen_Subscription_Model_Resource_Subscription_Collection
     */
    public function getSubscriptions()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        $subscriptions = Mage::getModel('adyen_subscription/subscription')->getCollection()
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->addBillingAgreementToSelect();

        return $subscriptions;
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return string
     */
    public function getViewUrl($subscription)
    {
        return $this->getUrl('adyen_subscription/customer/view', array('subscription_id' => $subscription->getId()));
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return string
     */
    public function getAgreementUrl($subscription)
    {
        $agreementId = $subscription->getBillingAgreementId();

        return $this->getUrl('sales/billing_agreement/view', array('agreement' => $agreementId));
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
