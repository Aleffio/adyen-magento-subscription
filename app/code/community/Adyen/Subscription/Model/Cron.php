<?php
/**
 * Adyen_Subscription
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
 * @category  Adyen
 * @package   Adyen_Subscription
 * @author    Paul Hachmang – H&O <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */
 
class Adyen_Subscription_Model_Cron
{

    /**
     * @cron adyen_subscription_create_subscriptions
     * @return string
     */
    public function createSubscriptions()
    {
        $collection = Mage::getModel('sales/order')->getCollection();

        $resource = $collection->getResource();

        $collection->getSelect()->joinLeft(
            array('subscription' => $resource->getTable('adyen_subscription/subscription')),
            'main_table.entity_id = subscription.order_id',
            array('created_subscription_id' => 'entity_id')
        );
        $collection->getSelect()->joinLeft(
            array('oi' => $resource->getTable('sales/order_item')),
            'main_table.entity_id = oi.order_id',
            array('oi.item_id', 'oi.parent_item_id', 'oi.product_options')
        );

        $collection->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $collection->addFieldToFilter('subscription.entity_id', array('null' => true));
        $collection->addFieldToFilter('parent_item_id', array('null' => true));
        $collection->addFieldToFilter('product_options', array('nlike' => '%;s:18:"adyen_subscription";s:4:"none"%'));

        $collection->getSelect()->group('main_table.entity_id');

        $o = $p = $e = 0;
        foreach ($collection as $order) {
            try {
                $subscriptions = Mage::getModel('adyen_subscription/service_order')->createSubscription($order);

                foreach ($subscriptions as $subscription) {
                    /** @var Adyen_Subscription_Model_Subscription $subscription */
                    $message = Mage::helper('adyen_subscription')->__('Created a subscription (#%s) from order.', $subscription->getId());
                    $order->addStatusHistoryComment($message);
                    $order->save();
                    $p++;
                }
                $o++;
            }
            catch (Exception $exception) {
                $e++;
                Adyen_Subscription_Exception::logException($exception);
            }
        }

        return Mage::helper('adyen_subscription')->__(
            '%s orders processed, %s subscriptions created (%s errors)', $o, $p, $e
        );
    }


    /**
     * @cron adyen_subscription_create_quotes
     * @return string
     */
    public function createQuotes()
    {
        $subscriptionCollection = Mage::getResourceModel('adyen_subscription/subscription_collection');
        $subscriptionCollection->addScheduleQuoteFilter();

        if ($subscriptionCollection->count() <= 0) {
            return '';
        }

        $timezone = new DateTimeZone(Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        ));
        $scheduleBefore = new DateTime('now', $timezone);
        $scheduleBefore->add(new DateInterval('P2W'));

        $successCount = 0;
        $failureCount = 0;
        foreach ($subscriptionCollection as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */

            if ($subscription->getScheduledAt()) {
                $timezone = new DateTimeZone(Mage::getStoreConfig(
                    Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
                ));
                $scheduleDate = new DateTime($subscription->getScheduledAt(), $timezone);
            }
            else {
                $scheduleDate = $subscription->calculateNextScheduleDate(true);
            }

            $subscription->setScheduledAt($scheduleDate->format('Y-m-d H:i:s'));

            if ($scheduleDate < $scheduleBefore) {
                try {
                    Mage::getSingleton('adyen_subscription/service_subscription')->createQuote($subscription);
                    $successCount++;
                } catch (Exception $e) {
                    Adyen_Subscription_Exception::logException($e);
                    $failureCount++;
                }
            }
        }

        return Mage::helper('adyen_subscription')->__(
            'Quotes created, %s successful, %s failed', $successCount, $failureCount
        );
    }


    /**
     * @cron adyen_subscription_create_orders
     * @return string
     */
    public function createOrders()
    {
        $subscriptionCollection = Mage::getResourceModel('adyen_subscription/subscription_collection');
        $subscriptionCollection->addPlaceOrderFilter();

        if ($subscriptionCollection->count() <= 0) {
            return '';
        }

        $successCount = 0;
        $failureCount = 0;
        foreach ($subscriptionCollection as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */

            try {
                $quote = $subscription->getActiveQuote();
                if (! $quote) {
                    Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
                }

                Mage::getSingleton('adyen_subscription/service_quote')->createOrder($subscription->getActiveQuote(), $subscription);
                $successCount++;
            } catch (Exception $e) {
                Adyen_Subscription_Exception::logException($e);
                $failureCount++;
            }
        }

        return Mage::helper('adyen_subscription')->__(
            'Quotes created, %s successful, %s failed', $successCount, $failureCount
        );
    }

    /**
     * @cron adyen_subscription_create_update_prices
     */
    public function updatePrices()
    {
        $productSubscriptionCollection = Mage::getResourceModel('adyen_subscription/product_subscription_collection')
            ->addFieldToFilter('update_price', 1)
            ->setPageSize(100);

        $subscriptionIds = [];
        foreach ($productSubscriptionCollection as $productSubscription) {
            /** @var Adyen_Subscription_Model_Product_Subscription $productSubscription */
            $subscriptionItemCollection = Mage::getResourceModel('adyen_subscription/subscription_item_collection')
                ->addFieldToFilter('product_subscription_id', $productSubscription->getId());

            foreach ($subscriptionItemCollection as $subscriptionItem) {
                $taxHelper = Mage::helper('tax');
                $subscription = $subscriptionItem->getSubscription();

                $priceInclTax = $taxHelper->getPrice(
                    $productSubscription->getProduct(),
                    $productSubscription->getPrice(),
                    true,
                    $subscription->getShippingAddress(),
                    $subscription->getBillingAddress(),
                    $subscription->getCustomer()->getTaxClassId(),
                    $subscription->getStoreId()
                );

                $price = $taxHelper->getPrice(
                    $productSubscription->getProduct(),
                    $productSubscription->getPrice(),
                    false,
                    $subscription->getShippingAddress(),
                    $subscription->getBillingAddress(),
                    $subscription->getCustomer()->getTaxClassId(),
                    $subscription->getStoreId()
                );

                $subscriptionItem->setPriceInclTax($priceInclTax);
                $subscriptionItem->setPrice($price);

                $subscriptionItem->save();
                $subscriptionIds[] = $subscriptionItem->getSubscriptionId();

                if ($quote = $subscription->getActiveQuote()) {
                    $quote->setTotalsCollectedFlag(false)
                          ->collectTotals();

                    $quote->save();
                }
            }

            $productSubscription->setUpdatePrice(0);
            $productSubscription->save();
        }
    }
}