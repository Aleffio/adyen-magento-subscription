<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_Model_Service_Quote
{

    /**
     * @param Mage_Sales_Model_Quote     $quote
     * @param Ho_Recurring_Model_Profile $profile
     *
     * @return Mage_Sales_Model_Order
     * @throws Ho_Recurring_Exception|Exception
     */
    public function createOrder(
        Mage_Sales_Model_Quote $quote,
        Ho_Recurring_Model_Profile $profile
    ) {
        try {
            if (! $profile->canCreateOrder()) {
                Ho_Recurring_Exception::throwException(
                    Mage::helper('ho_recurring')->__('Not allowed to create order from quote')
                );
            }
            foreach ($quote->getAllItems() as $item) {
                /** @var Mage_Sales_Model_Quote_Item $item */
                $item->getProduct()->setData('is_created_from_profile_item', $item->getData('recurring_profile_item_id'));
            }

            $quote->collectTotals();
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $order = $service->getOrder();

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
     * @param Ho_Recurring_Model_Profile $profile
     * @return Ho_Recurring_Model_Profile $profile
     */
    public function updateProfile(
        Mage_Sales_Model_Quote $quote,
        Ho_Recurring_Model_Profile $profile
    ) {
        $term = $termType = $stockId = null;
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $productProfile = $this->_getProductProfile($quoteItem);

            if (!$productProfile) {
                // No recurring product profile found, no recurring profile needs to be created
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
                Ho_Recurring_Exception::throwException('Recurring profiles of products in quote have different terms');
            }
        }

        $billingAgreement = $this->_getBillingAgreement($quote);

        if (!$quote->getShippingAddress()->getShippingMethod()) {
            Ho_Recurring_Exception::throwException('No shipping method selected');
        }

        // Update profile
        $profile->setStatus(Ho_Recurring_Model_Profile::STATUS_ACTIVE)
            ->setStockId($stockId)
            ->setBillingAgreementId($billingAgreement->getId())
            ->setTerm($term)
            ->setTermType($termType)
            ->setShippingMethod($quote->getShippingAddress()->getShippingMethod())
            ->setUpdatedAt(now())
            ->save();

        // Delete current profile items
        foreach ($profile->getItemCollection() as $profileItem) {
            /** @var Ho_Recurring_Model_Profile_Item $profileItem */
            $profileItem->delete();
        }

        $i = 0;
        // Create new profile items
        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */

            /** @var Ho_Recurring_Model_Product_Profile $productProfile */
            $productProfile = $this->_getProductProfile($quoteItem);

            if (!$productProfile) {
                // No recurring product profile found, no recurring profile needs to be created
                continue;
            }

            $productOptions = [];
            $productOptions['info_buyRequest'] = unserialize($quoteItem->getOptionByCode('info_buyRequest')->getValue());
            $productOptions['additional_options'] = unserialize($quoteItem->getOptionByCode('additional_options')->getValue());

            /** @var Ho_Recurring_Model_Profile_Item $profileItem */
            $profileItem = Mage::getModel('ho_recurring/profile_item')
                ->setProfileId($profile->getId())
                ->setStatus(Ho_Recurring_Model_Profile_Item::STATUS_ACTIVE)
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
            Ho_Recurring_Exception::throwException('No recurring products in the profile');
        }

        return $profile;
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

        $method = explode('_', $quotePayment->getMethod());
        $recurringReference = $method[count($method) - 1];

        $billingAgreement = Mage::getModel('sales/billing_agreement')
            ->getCollection()
            ->addFieldToFilter('reference_id', $recurringReference)
            ->getFirstItem();

        if (! $billingAgreement->getId()) {
            Ho_Recurring_Exception::throwException('Could not find billing agreement for quote ' . $quote->getId());
        }

        return $billingAgreement;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return Ho_Recurring_Model_Product_Profile
     */
    protected function _getProductProfile(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $profileId = $quoteItem->getBuyRequest()->getData('ho_recurring_profile');
        if (! $profileId) {
            return false;
        }

        $recurringProductProfile = Mage::getModel('ho_recurring/product_profile')
            ->load($profileId);

        if (!$recurringProductProfile->getId()) {
            return false;
        }

        return $recurringProductProfile;
    }
}
