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
     * @param Adyen_Subscription_Model_Subscription $subscription
     *
     * @return Mage_Sales_Model_Order
     * @throws Adyen_Subscription_Exception|Exception
     */
    public function createOrder(
        Mage_Sales_Model_Quote $quote,
        Adyen_Subscription_Model_Subscription $subscription
    ) {
        try {
            if (! $subscription->canCreateOrder()) {
                Mage::helper('adyen_subscription')->logOrderCron("Not allowed to create order from quote");
                Adyen_Subscription_Exception::throwException(
                    Mage::helper('adyen_subscription')->__('Not allowed to create order from quote')
                );
            }
            foreach ($quote->getAllItems() as $item) {
                /** @var Mage_Sales_Model_Quote_Item $item */
                $item->getProduct()->setData('is_created_from_subscription_item', $item->getData('subscription_item_id'));
            }

            $quote->collectTotals();
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $order = $service->getOrder();

            // Save order addresses at subscription when they're currently quote addresses
            $subscriptionBillingAddress = Mage::getModel('adyen_subscription/subscription_address')
                ->getSubscriptionAddress($subscription, Adyen_Subscription_Model_Subscription_Address::ADDRESS_TYPE_BILLING);

            if ($subscriptionBillingAddress->getSource() == Adyen_Subscription_Model_Subscription_Address::ADDRESS_SOURCE_QUOTE) {
                $subscriptionBillingAddress
                    ->initAddress($subscription, $order->getBillingAddress())
                    ->save();
            }

            $subscriptionShippingAddress = Mage::getModel('adyen_subscription/subscription_address')
                ->getSubscriptionAddress($subscription, Adyen_Subscription_Model_Subscription_Address::ADDRESS_TYPE_SHIPPING);

            if ($subscriptionShippingAddress->getSource() == Adyen_Subscription_Model_Subscription_Address::ADDRESS_SOURCE_QUOTE) {
                $subscriptionShippingAddress
                    ->initAddress($subscription, $order->getShippingAddress())
                    ->save();
            }

            $orderAdditional = $subscription->getOrderAdditional($service->getOrder(), true)->save();
            $quoteAdditional = $subscription->getActiveQuoteAdditional()->setOrder($order)->save();

            $subscription->setErrorMessage(null);
            if ($subscription->getStatus() == $subscription::STATUS_ORDER_ERROR) {
                $subscription->setStatus($subscription::STATUS_ACTIVE);
            }

            $subscription->setScheduledAt($subscription->calculateNextScheduleDate());

            Mage::getModel('core/resource_transaction')
                ->addObject($subscription)
                ->addObject($orderAdditional)
                ->addObject($quoteAdditional)
                ->save();

            Mage::helper('adyen_subscription')->logOrderCron(sprintf("Successful created order (#%s) for subscription (#%s)" , $order->getId(), $subscription->getId()));

            return $service->getOrder();

        } catch (Mage_Payment_Exception $e) {
            Mage::helper('adyen_subscription')->logOrderCron(sprintf("Error in subscription (#%s) creating order from quote (#%s) error is: %s", $subscription->getId(), $quote->getId(), $e->getMessage()));
            $subscription->setStatus($subscription::STATUS_PAYMENT_ERROR);
            $subscription->setErrorMessage($e->getMessage());
            $subscription->save();
            throw $e;
        } catch (Exception $e) {
            Mage::helper('adyen_subscription')->logOrderCron(sprintf("Error in subscription (#%s) creating order from quote (#%s) error is: %s", $subscription->getId(), $quote->getId(), $e->getMessage()));
            $subscription->setStatus($subscription::STATUS_ORDER_ERROR);
            $subscription->setErrorMessage($e->getMessage());
            $subscription->save();
            throw $e;
        }
    }

    /**
     * Update subscription based on given quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @return Adyen_Subscription_Model_Subscription $subscription
     */
    public function updateSubscription(
        Mage_Sales_Model_Quote $quote,
        Adyen_Subscription_Model_Subscription $subscription
    ) {
        Mage::dispatchEvent('adyen_subscription_quote_updatesubscription_before', array('subscription' => $subscription, 'quote' => $quote));

        $term = $termType = $stockId = null;
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $productSubscription = $this->_getProductSubscription($quoteItem);

            if (!$productSubscription) {
                // No product subscription found, no subscription needs to be created
                continue;
            }

            if (is_null($stockId)) {
                $stockId = $quoteItem->getStockId();
            }

            if (is_null($term)) {
                $term = $productSubscription->getTerm();
            }
            if (is_null($termType)) {
                $termType = $productSubscription->getTermType();
            }
            if ($term != $productSubscription->getTerm() || $termType != $productSubscription->getTermType()) {
                Adyen_Subscription_Exception::throwException('Adyen Subscription options of products in quote have different terms');
            }
        }

        $billingAgreement = $this->getBillingAgreement($quote);

        $this->updateQuotePayment($quote, $billingAgreement);

        if (!$quote->getShippingAddress()->getShippingMethod()) {
            Adyen_Subscription_Exception::throwException('No shipping method selected');
        }

        // Update subscription
        $subscription->setStatus(Adyen_Subscription_Model_Subscription::STATUS_ACTIVE)
            ->setStockId($stockId)
            ->setBillingAgreementId($billingAgreement->getId())
            ->setTerm($term)
            ->setTermType($termType)
            ->setShippingMethod($quote->getShippingAddress()->getShippingMethod())
            ->setUpdatedAt(now())
            ->save();

        // Create subscription addresses
        $subscriptionBillingAddress = Mage::getModel('adyen_subscription/subscription_address')
            ->getSubscriptionAddress($subscription, Adyen_Subscription_Model_Subscription_Address::ADDRESS_TYPE_BILLING)
            ->initAddress($subscription, $quote->getBillingAddress())
            ->save();

        $subscriptionShippingAddress = Mage::getModel('adyen_subscription/subscription_address')
            ->getSubscriptionAddress($subscription, Adyen_Subscription_Model_Subscription_Address::ADDRESS_TYPE_SHIPPING)
            ->initAddress($subscription, $quote->getShippingAddress())
            ->save();

        // Delete current subscription items
        foreach ($subscription->getItemCollection() as $subscriptionItem) {
            /** @var Adyen_Subscription_Model_Subscription_Item $subscriptionItem */
            $subscriptionItem->delete();
        }

        $i = 0;
        // Create new subscription items
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */

            /** @var Adyen_Subscription_Model_Product_Subscription $productSubscription */
            $productSubscription = $this->_getProductSubscription($quoteItem);

            if (!$productSubscription) {
                // No product subscription found, no subscription needs to be created
                continue;
            }

            $productOptions = array();
            $productOptions['info_buyRequest'] = unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
            $productOptions['additional_options'] = unserialize($quoteItem->getOptionByCode('additional_options')->getValue());

            /** @var Adyen_Subscription_Model_Subscription_Item $subscriptionItem */
            $subscriptionItem = Mage::getModel('adyen_subscription/subscription_item')
                ->setSubscriptionId($subscription->getId())
                ->setStatus(Adyen_Subscription_Model_Subscription_Item::STATUS_ACTIVE)
                ->setProductId($quoteItem->getProductId())
                ->setProductOptions(serialize($productOptions))
                ->setSku($quoteItem->getSku())
                ->setName($quoteItem->getName())
                ->setLabel($productSubscription->getLabel())
                ->setPrice($quoteItem->getPrice())
                ->setPriceInclTax($quoteItem->getPriceInclTax())
                ->setQty($quoteItem->getQty())
                ->setOnce(0)
                // Currently not in use
//                ->setMinBillingCycles($productSubscription->getMinBillingCycles())
//                ->setMaxBillingCycles($productSubscription->getMaxBillingCycles())
                ->setCreatedAt(now())
                ->save();

            $i++;
        }

        if ($i <= 0) {
            Adyen_Subscription_Exception::throwException('No subscription products in the subscription');
        }
        
        Mage::dispatchEvent('adyen_subscription_quote_updatesubscription_after', array('subscription' => $subscription, 'quote' => $quote));

        return $subscription;
    }

    /**
     * The additional info and cc type of a quote payment are not updated when
     * selecting another payment method while editing a subscription or subscription quote,
     * but they have to be updated for the payment method to be valid
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Quote
     * @throws Exception
     */
    public function updateQuotePayment(Mage_Sales_Model_Quote $quote, Adyen_Payment_Model_Billing_Agreement $billingAgreement)
    {
        $subscriptionDetailReference = str_replace('adyen_oneclick_', '', $quote->getPayment()->getData('method'));

        $quote->getPayment()->setAdditionalInformation('recurring_detail_reference', $subscriptionDetailReference);

        $agreementData = $billingAgreement->getAgreementData();
        if(isset($agreementData['variant'])) {
            $quote->getPayment()->setCcType($agreementData['variant']);
        } else {
            $quote->getPayment()->setCcType(null);
        }

        $quote->getPayment()->save();

        return $quote;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Sales_Model_Billing_Agreement
     */
    public function getBillingAgreement(Mage_Sales_Model_Quote $quote)
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
     * @return Adyen_Subscription_Model_Product_Subscription
     */
    protected function _getProductSubscription(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $subscriptionId = $quoteItem->getBuyRequest()->getData('adyen_subscription');
        if (! $subscriptionId) {
            return false;
        }

        $subscriptionProductSubscription = Mage::getModel('adyen_subscription/product_subscription')
            ->load($subscriptionId);

        if (!$subscriptionProductSubscription->getId()) {
            return false;
        }

        return $subscriptionProductSubscription;
    }
}
