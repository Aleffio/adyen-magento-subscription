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

class Adyen_Subscription_Helper_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve product subscription, if product is a subscription, else false
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Adyen_Subscription_Model_Product_Subscription|false
     */
    public function getProductSubscription($product)
    {
        if (isset($product->getAttributes()['adyen_subscription_type'])) {
            if ($product->getData('adyen_subscription_type') != Adyen_Subscription_Model_Product_Subscription::TYPE_DISABLED) {
                $option = $product->getCustomOption('additional_options');

                if ($option) {
                    $additionalOptions = unserialize($option->getValue());
                    foreach ($additionalOptions as $additional) {
                        if ($additional['code'] == 'adyen_subscription') {
                            if ($additional['option_value'] != 'none') {
                                $subscription = Mage::getModel('adyen_subscription/product_subscription')->load($additional['option_value']);
                                if (! $subscription->getId()) {
                                    return false;
                                }

                                return $subscription;
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
     * @return Adyen_Subscription_Model_Subscription_Item|false
     */
    public function getSubscriptionItem($product)
    {
        if ($subscriptionItemId = $product->getData('is_created_from_subscription_item')) {
            $subscriptionItem = Mage::getModel('adyen_subscription/subscription_item')->load($subscriptionItemId);

            if ($subscriptionItem->getId()) {
                return $subscriptionItem;
            }
        }

        return false;
    }
}
