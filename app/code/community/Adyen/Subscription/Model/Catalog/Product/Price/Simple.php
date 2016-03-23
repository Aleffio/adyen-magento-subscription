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
     * When configured that catalog prices are including tax and subscription pricee excluding tax,
     * the subscription item prices of new orders change when tax percentage is changed
     *
     * @param float|null $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        if ($subscriptionItem = $this->_helper()->getSubscriptionItem($product)) {
            $subscription = $subscriptionItem->getSubscription(); // @todo Performance

            $store = $product->getStore();
            $configCatalogInclTax = Mage::getModel('tax/config')->priceIncludesTax($store);
            $useSubscriptionPricesIncTax = Mage::helper('adyen_subscription/config')->getPriceIncludesTax($store);

            if ($configCatalogInclTax && $useSubscriptionPricesIncTax) {
                return $subscriptionItem->getPriceInclTax();
            }
            if (! $configCatalogInclTax && ! $useSubscriptionPricesIncTax) {
                return $subscriptionItem->getPrice();
            }
            if ($configCatalogInclTax && ! $useSubscriptionPricesIncTax) {
                $priceExclTax = $subscriptionItem->getPrice();

                $customerPercent = Mage::helper('adyen_subscription/quote')
                    ->getCustomerTaxPercent($subscription, $product);
                $customerTax = Mage::getSingleton('tax/calculation')
                    ->calcTaxAmount($priceExclTax, $customerPercent, false, false);

                $customerPriceInclTax = $store->roundPrice($priceExclTax + $customerTax);

                return $customerPriceInclTax;
            }

            if (! $configCatalogInclTax && $useSubscriptionPricesIncTax) {
                $message = 'Please fix the tax settings;'
                    . ' it\'s not possible to set catalog prices to excl. tax and subscription prices to incl. tax';
                Adyen_Subscription_Exception::throwException($message);
            }
        }

        if ($subscription = $this->_helper()->getProductSubscription($product)) {
            return $this->_applyOptionsPrice($product, $qty, $subscription->getPrice());
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
