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

class Adyen_Subscription_Model_Service_Quote
{
    /**
     * @param Mage_Sales_Model_Quote     $quote
     * @param Adyen_Subscription_Model_Profile $profile
     *
     * @return Mage_Sales_Model_Order
     * @throws Adyen_Subscription_Exception|Exception
     */
    public function createOrder(
        Mage_Sales_Model_Quote $quote,
        Adyen_Subscription_Model_Profile $profile
    ) {
        try {
            if (! $profile->canCreateOrder()) {
                Adyen_Subscription_Exception::throwException(
                    Mage::helper('adyen_subscription')->__('Not allowed to create order from quote')
                );
            }
            foreach ($quote->getAllItems() as $item) {
                /** @var Mage_Sales_Model_Quote_Item $item */
                $item->getProduct()->setData('is_created_from_profile_item', $item->getData('subscription_item_id'));
            }

            $quote->collectTotals();
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $order = $service->getOrder();

            // Save order addresses at profile when they're currently quote addresses
            $profileBillingAddress = Mage::getModel('adyen_subscription/profile_address')
                ->getProfileAddress($profile, Adyen_Subscription_Model_Profile_Address::ADDRESS_TYPE_BILLING);

            if ($profileBillingAddress->getSource() == Adyen_Subscription_Model_Profile_Address::ADDRESS_SOURCE_QUOTE) {
                $profileBillingAddress
                    ->initAddress($profile, $order->getBillingAddress())
                    ->save();
            }

            $profileShippingAddress = Mage::getModel('adyen_subscription/profile_address')
                ->getProfileAddress($profile, Adyen_Subscription_Model_Profile_Address::ADDRESS_TYPE_SHIPPING);

            if ($profileShippingAddress->getSource() == Adyen_Subscription_Model_Profile_Address::ADDRESS_SOURCE_QUOTE) {
                $profileShippingAddress
                    ->initAddress($profile, $order->getShippingAddress())
                    ->save();
            }

            $orderAdditional = $profile->getOrderAdditional($service->getOrder(), true)->save();
            $quoteAdditional = $profile->getActiveQuoteAdditional()->setOrder($order)->save();

            $profile->setErrorMessage(null);
            if ($profile->getStatus() == $profile::STATUS_ORDER_ERROR) {
                $profile->setStatus($profile::STATUS_ACTIVE);
            }

            $profile->setScheduledAt($profile->calculateNextScheduleDate());

            Mage::getModel('core/resource_transaction')
                ->addObject($profile)
                ->addObject($orderAdditional)
                ->addObject($quoteAdditional)
                ->save();

            return $service->getOrder();

        } catch (Mage_Payment_Exception $e) {
            $profile->setStatus($profile::STATUS_PAYMENT_ERROR);
            $profile->setErrorMessage($e->getMessage());
            $profile->save();
            throw $e;
        } catch (Exception $e) {
            $profile->setStatus($profile::STATUS_ORDER_ERROR);
            $profile->setErrorMessage($e->getMessage());
            $profile->save();
            throw $e;
        }
    }

    /**
     * Update profile based on given quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Adyen_Subscription_Model_Profile $profile
     * @return Adyen_Subscription_Model_Profile $profile
     */
    public function updateProfile(
        Mage_Sales_Model_Quote $quote,
        Adyen_Subscription_Model_Profile $profile
    ) {
        $term = $termType = $stockId = null;
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $productProfile = $this->_getProductProfile($quoteItem);

            if (!$productProfile) {
                // No product subscription found, no subscription needs to be created
                continue;
            }

            if (is_null($stockId)) {
                $stockId = $quoteItem->getStockId();
            }

            if (is_null($term)) {
                $term = $productProfile->getTerm();
            }
            if (is_null($termType)) {
                $termType = $productProfile->getTermType();
            }
            if ($term != $productProfile->getTerm() || $termType != $productProfile->getTermType()) {
                Adyen_Subscription_Exception::throwException('Subscription options of products in quote have different terms');
            }
        }

        $this->updateQuotePayment($quote);

        $billingAgreement = $this->_getBillingAgreement($quote);

        if (!$quote->getShippingAddress()->getShippingMethod()) {
            Adyen_Subscription_Exception::throwException('No shipping method selected');
        }

        // Update profile
        $profile->setStatus(Adyen_Subscription_Model_Profile::STATUS_ACTIVE)
            ->setStockId($stockId)
            ->setBillingAgreementId($billingAgreement->getId())
            ->setTerm($term)
            ->setTermType($termType)
            ->setShippingMethod($quote->getShippingAddress()->getShippingMethod())
            ->setUpdatedAt(now())
            ->save();

        // Create profile addresses
        $profileBillingAddress = Mage::getModel('adyen_subscription/profile_address')
            ->getProfileAddress($profile, Adyen_Subscription_Model_Profile_Address::ADDRESS_TYPE_BILLING)
            ->initAddress($profile, $quote->getBillingAddress())
            ->save();

        $profileShippingAddress = Mage::getModel('adyen_subscription/profile_address')
            ->getProfileAddress($profile, Adyen_Subscription_Model_Profile_Address::ADDRESS_TYPE_SHIPPING)
            ->initAddress($profile, $quote->getShippingAddress())
            ->save();

        // Delete current profile items
        foreach ($profile->getItemCollection() as $profileItem) {
            /** @var Adyen_Subscription_Model_Profile_Item $profileItem */
            $profileItem->delete();
        }

        $i = 0;
        // Create new profile items
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */

            /** @var Adyen_Subscription_Model_Product_Profile $productProfile */
            $productProfile = $this->_getProductProfile($quoteItem);

            if (!$productProfile) {
                // No subscription profile found, no subscription needs to be created
                continue;
            }

            $productOptions = [];
            $productOptions['info_buyRequest'] = unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
            $productOptions['additional_options'] = unserialize($quoteItem->getOptionByCode('additional_options')->getValue());

            /** @var Adyen_Subscription_Model_Profile_Item $profileItem */
            $profileItem = Mage::getModel('adyen_subscription/profile_item')
                ->setProfileId($profile->getId())
                ->setStatus(Adyen_Subscription_Model_Profile_Item::STATUS_ACTIVE)
                ->setProductId($quoteItem->getProductId())
                ->setProductOptions(serialize($productOptions))
                ->setSku($quoteItem->getSku())
                ->setName($quoteItem->getName())
                ->setLabel($productProfile->getLabel())
                ->setPrice($quoteItem->getPrice())
                ->setPriceInclTax($quoteItem->getPriceInclTax())
                ->setQty($quoteItem->getQty())
                ->setOnce(0)
                // Currently not in use
//                ->setMinBillingCycles($productProfile->getMinBillingCycles())
//                ->setMaxBillingCycles($productProfile->getMaxBillingCycles())
                ->setCreatedAt(now())
                ->save();

            $i++;
        }

        if ($i <= 0) {
            Adyen_Subscription_Exception::throwException('No subscription products in the subscription');
        }

        return $profile;
    }

    /**
     * The additional info and cc type of a quote payment are not updated when
     * selecting another payment method while editing a profile or profile quote,
     * but they have to be updated for the payment method to be valid
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     */
    public function updateQuotePayment(Mage_Sales_Model_Quote $quote)
    {
        $subscriptionDetailReference = str_replace('adyen_oneclick_', '', $quote->getPayment()->getData('method'));

        $quote->getPayment()->setAdditionalInformation('subscription_detail_reference', $subscriptionDetailReference);
        $quote->getPayment()->setCcType(null);
        $quote->getPayment()->save();

        return $quote;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _getBillingAgreement(Mage_Sales_Model_Quote $quote)
    {
        /** @var Mage_Sales_Model_Quote_Payment $quotePayment */
        $quotePayment = Mage::getModel('sales/quote_payment')
            ->getCollection()
            ->addFieldToFilter('quote_id', $quote->getId())
            ->getFirstItem();

        $subscriptionReference = str_replace('adyen_oneclick_', '', $quotePayment->getMethod());

        $billingAgreement = Mage::getModel('sales/billing_agreement')
            ->getCollection()
            ->addFieldToFilter('reference_id', $subscriptionReference)
            ->getFirstItem();

        if (! $billingAgreement->getId()) {
            Adyen_Subscription_Exception::throwException('Could not find billing agreement for quote ' . $quote->getId());
        }

        return $billingAgreement;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return Adyen_Subscription_Model_Product_Profile
     */
    protected function _getProductProfile(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $profileId = $quoteItem->getBuyRequest()->getData('adyen_subscription_profile');
        if (! $profileId) {
            return false;
        }

        $subscriptionProductProfile = Mage::getModel('adyen_subscription/product_profile')
            ->load($profileId);

        if (!$subscriptionProductProfile->getId()) {
            return false;
        }

        return $subscriptionProductProfile;
    }
}
