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

class Adyen_Subscription_Block_Customer_Profiles_View extends Mage_Core_Block_Template
{
    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        $profile = Mage::registry('adyen_subscription_profile');

        return $profile;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('adyen_subscription/customer/profiles');
    }
}
