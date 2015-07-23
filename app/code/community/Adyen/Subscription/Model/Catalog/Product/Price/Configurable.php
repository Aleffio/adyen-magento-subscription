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

class Adyen_Subscription_Model_Catalog_Product_Price_Configurable extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    /**
     * Get product final price
     * Extended to return subscription price when product is a subscription product
     *
     * @param float|null $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        if ($subscriptionItem = $this->_helper()->getSubscriptionItem($product)) {
            return $subscriptionItem->getPriceInclTax();
        }

        if ($subscription = $this->_helper()->getProductSubscription($product)) {
            return $subscription->getPrice();
        }

        return parent::getFinalPrice($qty, $product);
    }

    /**
     * @return Adyen_Subscription_Helper_Quote
     */
    protected function _helper()
    {
        return Mage::helper('adyen_subscription/quote');
    }
}
