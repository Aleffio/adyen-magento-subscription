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
 * Class Adyen_Subscription_Model_Product_Profile_Label
 *
 * @method int getProfileId()
 * @method $this setProfileId(int $value)
 * @method int getStoreId()
 * @method $this setStoreId(int $value)
 * @method string getLabel()
 * @method $this setLabel(string $value)
 */
class Adyen_Subscription_Model_Product_Profile_Label extends Mage_Core_Model_Abstract
{
    protected function _construct ()
    {
        $this->_init('adyen_subscription/product_profile_label');
    }

    /**
     * @param Adyen_Subscription_Model_Product_Profile $profile
     * @param Mage_Core_Model_Store|int $store
     * @return $this
     */
    public function loadByProfile(Adyen_Subscription_Model_Product_Profile $profile, $store)
    {
        $labels = $this->getCollection()
            ->addFieldToFilter('profile_id', $profile->getId());

        if ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        }
        else {
            $storeId = $store;
        }

        $labels->addFieldToFilter('store_id', $storeId);

        return $labels->getFirstItem();
    }
}
