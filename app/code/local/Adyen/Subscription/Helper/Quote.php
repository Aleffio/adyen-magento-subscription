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

class Adyen_Subscription_Helper_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve product profile, if product is a subscription, else false
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Adyen_Subscription_Model_Product_Profile|false
     */
    public function getProductProfile($product)
    {
        if (isset($product->getAttributes()['adyen_subscription_type'])) {
            if ($product->getData('adyen_subscription_type') != Adyen_Subscription_Model_Product_Profile::TYPE_DISABLED) {
                $option = $product->getCustomOption('additional_options');

                if ($option) {
                    $additionalOptions = unserialize($option->getValue());
                    foreach ($additionalOptions as $additional) {
                        if ($additional['code'] == 'adyen_subscription_profile') {
                            if ($additional['option_value'] != 'none') {
                                $profile = Mage::getModel('adyen_subscription/product_profile')->load($additional['option_value']);
                                if (! $profile->getId()) {
                                    return false;
                                }

                                return $profile;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Adyen_Subscription_Model_Profile_Item|false
     */
    public function getProfileItem($product)
    {
        if ($profileItemId = $product->getData('is_created_from_profile_item')) {
            $profileItem = Mage::getModel('adyen_subscription/profile_item')->load($profileItemId);

            if ($profileItem->getId()) {
                return $profileItem;
            }
        }

        return false;
    }
}
