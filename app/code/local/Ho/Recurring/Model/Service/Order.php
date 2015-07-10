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

class Ho_Recurring_Model_Service_Order
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

            /** @var Ho_Recurring_Model_Product_Profile $productProfile */
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
            /** @var Ho_Recurring_Model_Profile $profile */
            $profile = Mage::getModel('ho_recurring/profile')
                ->setStatus(Ho_Recurring_Model_Profile::STATUS_ACTIVE)
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
                    Mage::helper('ho_recurring')->__('No billing agreement found')
                );
                $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            }

            $profile->save();

            $transactionItems = [];
            foreach ($productTerm['order_items'] as $orderItem) {
                /** @var Ho_Recurring_Model_Product_Profile $productProfile */
                $productProfile = $this->_getProductProfile($orderItem);

                // Create profile item
                /** @var Ho_Recurring_Model_Profile_Item $profileItem */
                $profileItem = Mage::getModel('ho_recurring/profile_item')
                    ->setProfileId($profile->getId())
                    ->setStatus(Ho_Recurring_Model_Profile_Item::STATUS_ACTIVE)
                    ->setProductId($orderItem->getProductId())
                    ->setProductOptions(serialize($orderItem->getProductOptions()))
                    ->setSku($orderItem->getSku())
                    ->setName($orderItem->getName())
                    ->setLabel($productProfile->getLabel())
                    ->setPrice($orderItem->getPrice())
                    ->setPriceInclTax($orderItem->getPriceInclTax())
                    ->setQty($orderItem->getQtyInvoiced())
                    ->setOnce(0)
                    // Currently not in use
//                    ->setMinBillingCycles($productProfile->getMinBillingCycles())
//                    ->setMaxBillingCycles($productProfile->getMaxBillingCycles())
                    ->setCreatedAt(now());

                $transactionItems[] = $profileItem;
            }

            // Create profile addresses
            $profileBillingAddress = Mage::getModel('ho_recurring/profile_address')
                ->initAddress($profile, $order->getBillingAddress())
                ->save();

            $profileShippingAddress = Mage::getModel('ho_recurring/profile_address')
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
            Ho_Recurring_Exception::logException(
                new Ho_Recurring_Exception('Could not find billing agreement for order '.$order->getIncrementId())
            );
            return false;
        }

        return Mage::getModel('sales/billing_agreement')->load($billingAgreementId);
    }

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Ho_Recurring_Model_Product_Profile
     */
    protected function _getProductProfile(Mage_Sales_Model_Order_Item $orderItem)
    {
        $profileId = $orderItem->getBuyRequest()->getData('ho_recurring_profile');
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
