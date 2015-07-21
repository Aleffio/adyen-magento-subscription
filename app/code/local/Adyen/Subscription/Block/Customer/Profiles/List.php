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

class Adyen_Subscription_Block_Customer_Profiles_List extends Mage_Core_Block_Template
{
    /**
     * @return Adyen_Subscription_Model_Resource_Profile_Collection
     */
    public function getProfiles()
    {
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

        $profiles = Mage::getModel('adyen_subscription/profile')->getCollection()
            ->addFieldToFilter('main_table.customer_id', $customerId)
            ->addBillingAgreementToSelect();

        return $profiles;
    }

    /**
     * @param Adyen_Subscription_Model_Profile $profile
     * @return string
     */
    public function getViewUrl($profile)
    {
        return $this->getUrl('adyen_subscription/customer/view', array('profile_id' => $profile->getId()));
    }

    /**
     * @param Adyen_Subscription_Model_Profile $profile
     * @return string
     */
    public function getAgreementUrl($profile)
    {
        $agreementId = $profile->getBillingAgreementId();

        return $this->getUrl('sales/billing_agreement/view', array('agreement' => $agreementId));
    }
}
