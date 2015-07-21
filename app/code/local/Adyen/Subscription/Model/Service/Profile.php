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

class Adyen_Subscription_Model_Service_Profile
{

    /**
     * @param Adyen_Subscription_Model_Profile $profile
     *
     * @return Mage_Sales_Model_Quote
     * @throws Adyen_Subscription_Exception|Exception
     */
    public function createQuote(Adyen_Subscription_Model_Profile $profile)
    {
        try {
            if (! $profile->canCreateQuote()) {
                Adyen_Subscription_Exception::throwException('Can not create quote from profile');
            }

            if ($quote = $profile->getActiveQuote()) {
                Adyen_Subscription_Exception::throwException('There is already an active quote present for this profile');
            }

            $storeId = $profile->getStoreId();

            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

            Mage::getSingleton('adminhtml/session_quote')->setStoreId($storeId);
            $customer = $profile->getCustomer();
            $quote = Mage::getModel('sales/quote')->assignCustomer($customer);
            $quote->setStoreId($storeId);
            $quote->setIsSuperMode(true);
            $quote->setIsActive(false); //always create an inactive quote, else it shows up on the frontend.
            $quote->setRecurringProfileId($profile->getId());
            $profile->setErrorMessage(null);

            // Add order items to quote
            foreach ($profile->getItemCollection() as $profileItem) {
                /** @var Adyen_Subscription_Model_Profile_Item $profileItem */
                $productId = $profileItem->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);
                $product->setData('is_created_from_profile_item', $profileItem->getId());

                $quoteItem = $quote->addProduct($product, $profileItem->getQty());

                if (! $quoteItem instanceof Mage_Sales_Model_Quote_Item) {
                    Adyen_Subscription_Exception::throwException(Mage::helper('adyen_subscription')->__(
                        'An error occurred while adding a product to the quote: %s', $quoteItem
                    ));
                }

                $quoteItem->setData('recurring_profile_item_id', $profileItem->getId());

                $quoteItem->setNoDiscount(false);
                $quoteItem->getProduct()->setIsSuperMode(true);

                $additionalData = Mage::getModel('sales/quote_item_option')->setData([
                    'code'       => 'additional_options',
                    'product_id' => $quoteItem->getProductId(),
                    'value'      => serialize($profileItem->getAdditionalOptions())
                ]);
                $quoteItem->addOption($additionalData);

                $buyRequest = Mage::getModel('sales/quote_item_option')->setData([
                    'code'       => 'info_buyRequest',
                    'product_id' => $quoteItem->getProductId(),
                    'value'      => serialize($profileItem->getBuyRequest())
                ]);
                $quoteItem->addOption($buyRequest);

                $quoteItem->checkData();
            }

            // Set billing address data
            /** @var Mage_Sales_Model_Quote_Address $billingAddress */
            $quote->getBillingAddress()
                ->addData($profile->getBillingAddressData())
                ->setData('email', $customer->getEmail());

            // Set shipping address data
            /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
            /** @noinspection PhpUndefinedMethodInspection */
            $quote->getShippingAddress()
                ->addData($profile->getShippingAddressData())
                ->setData('email', $customer->getEmail())
                ->setStockId($profile->getStockId())
                ->setCollectShippingRates(true)
                ->collectShippingRates();

            $quote->getShippingAddress()->collectTotals();

            // Set shipping method
            $shippingMethod = $profile->getShippingMethod();
            $quote->getShippingAddress()->setShippingMethod($shippingMethod)->save();

            if (! $profile->getBillingAgreement()->getId()) {
                Adyen_Subscription_Exception::throwException(Mage::helper('adyen_subscription')->__(
                    'No billing agreement found'
                ));
            }

            // Set payment method
            $methodInstance = $profile->getBillingAgreement()->getPaymentMethodInstance();

            if (! method_exists($methodInstance, 'initBillingAgreementPaymentInfo')) {
                Adyen_Subscription_Exception::throwException(Mage::helper('adyen_subscription')->__(
                    'Payment method %s does not support Adyen_Subscription', $methodInstance->getCode()
                ));
            }


            // Set billing agreement data
            /** @noinspection PhpUndefinedMethodInspection */
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $methodInstance->initBillingAgreementPaymentInfo($profile->getBillingAgreement(), $quote->getPayment());
            } catch(Mage_Core_Exception $e) {
                $profile->setErrorMessage($e->getMessage());
                $profile->setStatus($profile::STATUS_QUOTE_ERROR);
            }

            $quote->collectTotals();
            $profile->setActiveQuote($quote);
            $quoteAdditional = $profile->getActiveQuoteAdditional(true);

            Mage::getModel('core/resource_transaction')
                ->addObject($quote)
                ->addObject($profile)
                ->save();

            //we save in a second step because
            $quoteAdditional->setQuote($quote)->save();

            if ($profile->getStatus() == $profile::STATUS_QUOTE_ERROR) {
                $profile->setStatus($profile::STATUS_ACTIVE);
            }
            $profile->save();

            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

            return $quote;
        } catch (Exception $e) {
            $profile->setStatus($profile::STATUS_QUOTE_ERROR);
            $profile->setErrorMessage($e->getMessage());
            $profile->save();
            throw $e;
        }
    }
}
