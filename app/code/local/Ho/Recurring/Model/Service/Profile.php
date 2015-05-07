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
        $order = $profile->getOriginalOrder();

        $customerId = $profile->getCustomerId();
        $storeId = $profile->getStoreId();

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $quote = Mage::getModel('sales/quote')->assignCustomer($customer);
        $quote->setStoreId($storeId);

        // Add order items to quote
        foreach ($profile->getItems() as $item) {
            /** @var Ho_Recurring_Model_Profile_Item $item */
            $productId = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);

            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($product);
            $quoteItem->setQuote($quote);
            $quoteItem->setQty('1');
            $quoteItem->setStoreId($storeId);
            $quote->addItem($quoteItem);
        }

        // Set shipping address data
        /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress()
            ->addData($order->getShippingAddress()->getData())
            ->setData('email', $customer->getEmail());

        // Set billing address data
        /** @var Mage_Sales_Model_Quote_Address $billingAddress */
        $billingAddress = $quote->getBillingAddress()
            ->addData($order->getBillingAddress()->getData())
            ->setData('email', $customer->getEmail());

        // Collect shipping rates
        $shippingAddress->setStockId($order->getStockId());
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();

        $quote->getShippingAddress()->collectTotals();

        // Set shipping method
        $shippingMethod = $profile->getShippingMethod();
        $quote->getShippingAddress()->setShippingMethod($shippingMethod)->save();

        // Set payment method
        $paymentMethod = $profile->getPaymentMethod();
        $quote->getPayment()->importData(array('method' => $paymentMethod));
        $methodInstance = $quote->getPayment()->getMethodInstance();

        if (! method_exists($methodInstance, 'initRecurringProfileQuotePaymentInfo')) {
            Ho_Recurring_Exception::throwException(
                Mage::helper('ho_recurring')->__('Payment method %s does not support Ho_Recurring', $methodInstance->getCode()));
        }

        // Set billing agreement data
        $methodInstance->initBillingAgreementPaymentInfo($profile->getBillingAgreement(), $quote->getPayment());

        $quote->collectTotals();
        $quote->save();

        return $quote;
    }
}
