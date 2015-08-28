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

class Adyen_Subscription_Model_Catalog_Product_Price_Simple extends Mage_Catalog_Model_Product_Type_Price
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
     * Get product tier price by qty
     * Extended to hide tier pricing when product is a subscription product
     *
     * @param   float $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  float
     */
    public function getTierPrice($qty = null, $product)
    {
        if ($subscription = $this->_helper()->getProductSubscription($product)) {
            return array();
        }

        return parent::getTierPrice($qty, $product);
    }

    /**
     * @return Adyen_Subscription_Helper_Quote
     */
    protected function _helper()
    {
        return Mage::helper('adyen_subscription/quote');
    }
}
