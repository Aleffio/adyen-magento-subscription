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

class Adyen_Subscription_Model_Catalog_Product_Price_Bundle extends Mage_Bundle_Model_Product_Price
{
    /**
     * Retrieve product final price
     * Extended to return subscription price when product is a subscription product
     *
     * @param float|null $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        if ($profileItem = $this->_helper()->getProfileItem($product)) {
            return $profileItem->getPriceInclTax();
        }

        if ($profile = $this->_helper()->getProductProfile($product)) {
            return $profile->getPrice();
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
