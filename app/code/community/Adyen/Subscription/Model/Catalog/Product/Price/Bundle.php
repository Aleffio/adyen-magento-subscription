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
