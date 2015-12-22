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

    /**
     * Retrieve current tax percent for customer based on subscription and product
     *
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getCustomerTaxPercent(
        Adyen_Subscription_Model_Subscription $subscription,
        Mage_Catalog_Model_Product $product)
    {
        $percent = $product->getTaxPercent();
        $includingPercent = null;

        $taxClassId = $product->getTaxClassId();
        if (is_null($percent)) {
            if ($taxClassId) {
                $request = Mage::getSingleton('tax/calculation')
                    ->getRateRequest(
                        $subscription->getShippingAddress(),
                        $subscription->getBillingAddress(),
                        $subscription->getCustomer()->getTaxClassId(),
                        $product->getStore()
                    );
                $percent = Mage::getSingleton('tax/calculation')
                    ->getRate($request->setProductClassId($taxClassId));
            }
        }

        return $percent ?: 0;
    }

    /**
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @return array|bool
     */
    public function getProductAdditionalOptions(Varien_Object $buyRequest, Mage_Catalog_Model_Product $product)
    {
        $subscriptionId = $buyRequest->getData('adyen_subscription');
        if (! $subscriptionId) {
            return false;
        }

        Mage::helper('adyen_subscription/product')->loadProductSubscriptionData($product);
        if (! $product->getData('adyen_subscription_data')) {
            return false;
        }

        /** @var Adyen_Subscription_Model_Resource_Product_Subscription_Collection $subscriptionCollection */
        $subscriptionCollection = $product->getData('adyen_subscription_data');
        if ($subscriptionCollection->count() < 0) {
            return false;
        }

        /** @var Adyen_Subscription_Model_Product_Subscription $subscription */
        $subscription = $subscriptionCollection->getItemById($subscriptionId);

        if ($subscription) {
            $subscriptionOption = [
                'label'        => Mage::helper('adyen_subscription')->__('Subscription'),
                'code'         => 'adyen_subscription',
                'option_value' => $subscriptionId,
                'value'        => $subscription->getFrontendLabel(),
                'print_value'  => $subscription->getFrontendLabel(),
            ];
        }
        else {
            $subscriptionOption = [
                'label'        => Mage::helper('adyen_subscription')->__('Subscription'),
                'code'         => 'adyen_subscription',
                'option_value' => 'none',
                'value'        => Mage::helper('adyen_subscription')->__('No subscription'),
                'print_value'  => Mage::helper('adyen_subscription')->__('No subscription'),
            ];
        }

        return $subscriptionOption;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return bool|Adyen_Subscription_Model_Product_Subscription
     */
    public function getSubscriptionQtyByQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $subscriptionOption = $this->getSubscriptionOptionFromQuoteItem($quoteItem);

        if (! $subscriptionOption) {
            return false;
        }

        $productSubscription = Mage::getModel('adyen_subscription/product_subscription')
            ->load($subscriptionOption['option_value']);

        return $productSubscription;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return bool|array
     */
    public function getSubscriptionOptionFromQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $subscriptionOption = false;

        $additionalOptions = $quoteItem->getOptionByCode('additional_options');

        if ($quoteItem->getOptionByCode('additional_options')) {
            $additionalOptions = unserialize($additionalOptions->getValue());

            foreach ($additionalOptions as $option) {
                if ($option['code'] == 'adyen_subscription') {
                    $subscriptionOption = $option;
                    break;
                }
            }
        }

        return $subscriptionOption;
    }
}
