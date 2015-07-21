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

class Adyen_Subscription_Model_Service_Order
{
    /**
     * Create recurring profile(s) for given order.
     *
     * Order items that have the same term and term type are saved
     * in the same profile.
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function createProfile(Mage_Sales_Model_Order $order)
    {
        $profiles = [];

        if ($order->getRecurringProfileId()) {
            // Don't create profile, since this order is created by a profile
            return $profiles;
        }

        $productTerms = array();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */

            /** @var Adyen_Subscription_Model_Product_Profile $productProfile */
            $productProfile = $this->_getProductProfile($orderItem);

            if (!$productProfile) {
                // No recurring product profile found, no recurring profile needs to be created
                continue;
            }

            $arrayKey = $productProfile->getTerm().$productProfile->getTermType();

            $productTerms[$arrayKey]['term'] = $productProfile->getTerm();
            $productTerms[$arrayKey]['type'] = $productProfile->getTermType();
            $productTerms[$arrayKey]['order_items'][] = $orderItem;
        }

        // Create a profile for each term
        foreach ($productTerms as $productTerm) {
            $billingAgreement = $this->_getBillingAgreement($order);

            // Create profile
            /** @var Adyen_Subscription_Model_Profile $profile */
            $profile = Mage::getModel('adyen_subscription/profile')
                ->setStatus(Adyen_Subscription_Model_Profile::STATUS_ACTIVE)
                ->setStockId($order->getStockId())
                ->setCustomerId($order->getCustomerId())
                ->setCustomerName($order->getCustomerName())
                ->setOrderId($order->getId())
                ->setBillingAgreementId($billingAgreement ? $billingAgreement->getId(): null)
                ->setStoreId($order->getStoreId())
                ->setTerm($productTerm['term'])
                ->setTermType($productTerm['type'])
                ->setShippingMethod($order->getShippingMethod())
                ->setCreatedAt(now())
                ->setUpdatedAt(now());

            if (!$billingAgreement) {
                // No billing agreement could be found, profile is created,
                // but set profile directly to error
                $profile->setErrorMessage(
                    Mage::helper('adyen_subscription')->__('No billing agreement found')
                );
                $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            }

            $profile->save();

            $transactionItems = [];
            foreach ($productTerm['order_items'] as $orderItem) {
                /** @var Adyen_Subscription_Model_Product_Profile $productProfile */
                $productProfile = $this->_getProductProfile($orderItem);

                // Ordered qty is divided by product profile qty to get 'real' ordered qty
                $qty = $orderItem->getQtyInvoiced() / $productProfile->getQty();

                // Create profile item
                /** @var Adyen_Subscription_Model_Profile_Item $profileItem */
                $profileItem = Mage::getModel('adyen_subscription/profile_item')
                    ->setProfileId($profile->getId())
                    ->setStatus(Adyen_Subscription_Model_Profile_Item::STATUS_ACTIVE)
                    ->setProductId($orderItem->getProductId())
                    ->setProductOptions(serialize($orderItem->getProductOptions()))
                    ->setSku($orderItem->getSku())
                    ->setName($orderItem->getName())
                    ->setLabel($productProfile->getLabel())
                    ->setPrice($orderItem->getPrice())
                    ->setPriceInclTax($orderItem->getPriceInclTax())
                    ->setQty($qty)
                    ->setOnce(0)
                    // Currently not in use
//                    ->setMinBillingCycles($productProfile->getMinBillingCycles())
//                    ->setMaxBillingCycles($productProfile->getMaxBillingCycles())
                    ->setCreatedAt(now());

                $transactionItems[] = $profileItem;
            }

            // Create profile addresses
            $profileBillingAddress = Mage::getModel('adyen_subscription/profile_address')
                ->initAddress($profile, $order->getBillingAddress())
                ->save();

            $profileShippingAddress = Mage::getModel('adyen_subscription/profile_address')
                ->initAddress($profile, $order->getShippingAddress())
                ->save();

            /** @var Mage_Sales_Model_Quote $quote */
            $quote = Mage::getModel('sales/quote')
                ->setStore($order->getStore())
                ->load($order->getQuoteId());

            $profile->setActiveQuote($quote);
            $orderAdditional = $profile->getOrderAdditional($order, true)->save();
            $quoteAdditional = $profile->getActiveQuoteAdditional(true)
                ->setOrder($order);

            $scheduleDate = $profile->calculateNextScheduleDate();
            $profile->setScheduledAt($scheduleDate);

            $transaction = Mage::getModel('core/resource_transaction')
                ->addObject($profile)
                ->addObject($orderAdditional)
                ->addObject($quoteAdditional);

            foreach ($transactionItems as $item) {
                $transaction->addObject($item);
            }

            $transaction->save();

            $profiles[] = $profile;
        }

        return $profiles;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _getBillingAgreement(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();

        $select->from($resource->getTableName('sales/billing_agreement_order'));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('agreement_id');
        $select->where('order_id = ?', $order->getId());

        $billingAgreementId = $connection->fetchOne($select);
        if (! $billingAgreementId) {
            Adyen_Subscription_Exception::logException(
                new Adyen_Subscription_Exception('Could not find billing agreement for order '.$order->getIncrementId())
            );
            return false;
        }

        return Mage::getModel('sales/billing_agreement')->load($billingAgreementId);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Adyen_Subscription_Model_Product_Profile
     */
    protected function _getProductProfile(Mage_Sales_Model_Order_Item $orderItem)
    {
        $profileId = $orderItem->getBuyRequest()->getData('adyen_subscription_profile');
        if (! $profileId) {
            return false;
        }

        $recurringProductProfile = Mage::getModel('adyen_subscription/product_profile')
            ->load($profileId);

        if (!$recurringProductProfile->getId()) {
            return false;
        }

        return $recurringProductProfile;
    }
}
