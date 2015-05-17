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

class Ho_Recurring_Model_Service_Profile extends Mage_Core_Model_Abstract
{
    /**
     * @param Ho_Recurring_Model_Profile $profile
     * @return Mage_Sales_Model_Quote
     */
    public function createQuote(Ho_Recurring_Model_Profile $profile)
    {
        try {
            if (! $profile->canCreateQuote()) {
                Ho_Recurring_Exception::throwException('Can not create quote from profile');
            }

            if ($quote = $profile->getQuote()) {
                Ho_Recurring_Exception::throwException('There is already an active quote present for this profile');
            }

            $storeId = $profile->getStoreId();
            $customer = $profile->getCustomer();
            $quote = Mage::getModel('sales/quote')->assignCustomer($customer);
            $quote->setStoreId($storeId);
            $quote->setIsSuperMode(true);
            $quote->setIsActive(false); //always create an inactive quote, else it shows up on the frontend.

            // Add order items to quote
            foreach ($profile->getItems() as $profileItem) {
                /** @var Ho_Recurring_Model_Profile_Item $profileItem */
                $productId = $profileItem->getProductId();
                $product = Mage::getModel('catalog/product')->load($productId);

                $quoteItem = $quote->addProduct($product, $profileItem->getQty());

                $quoteItem->setCustomPrice($profileItem->getPrice());
                $quoteItem->setOriginalCustomPrice($profileItem->getPrice());
                $quoteItem->getProduct()->setIsSuperMode(true);

                $quoteItem->setCustomPrice($profileItem->getPriceInclTax());
                $quoteItem->setOriginalCustomPrice($profileItem->getPriceInclTax());
                $quoteItem->setNoDiscount(false);
                $quoteItem->getProduct()->setIsSuperMode(true);
                $quoteItem->checkData();
            }


            // Set billing address data
            /** @var Mage_Sales_Model_Quote_Address $billingAddress */
            $quote->getBillingAddress()
                ->addData($profile->getBillingAddressData())
                ->setData('email', $customer->getEmail());

            // Set shipping address data
            /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
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

            // Set payment method
            $quote->getPayment()->importData(array('method' => $profile->getBillingAgreement()->getMethodCode()));
            $methodInstance = $quote->getPayment()->getMethodInstance();

            if (! method_exists($methodInstance, 'initBillingAgreementPaymentInfo')) {
                Ho_Recurring_Exception::throwException(
                    Mage::helper('ho_recurring')->__('Payment method %s does not support Ho_Recurring', $methodInstance->getCode()));
            }

            // Set billing agreement data
            $methodInstance->initBillingAgreementPaymentInfo($profile->getBillingAgreement(), $quote->getPayment());

            $quote->collectTotals();
            $quote->save();

            $profile->saveQuoteAtProfile($quote);
            return $quote;

        } catch (Exception $e) {
            $profile->setStatus($profile::STATUS_QUOTE_ERROR);
            $profile->setErrorMessage($e->getMessage());
            throw $e;
        }
    }
}
