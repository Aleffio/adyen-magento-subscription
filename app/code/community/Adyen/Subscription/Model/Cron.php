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
 * @author    Paul Hachmang – H&O E-commerce specialists B.V. <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */

class Adyen_Subscription_Model_Cron
{

    /**
     * @return string
     */
    public function createSubscriptions()
    {
        Mage::helper('adyen_subscription')->logSubscriptionCron("Start subscription cronjob");
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

        $collection->getSelect()->joinLeft(
            array('bao' => $resource->getTable('sales/billing_agreement_order')),
            'main_table.entity_id = bao.order_id',
            array('agreement_id')
        );

        $collection->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $collection->addFieldToFilter('subscription.entity_id', array('null' => true));
        $collection->addFieldToFilter('parent_item_id', array('null' => true));
        $collection->addFieldToFilter('subscription_id', array('null' => true));
        $collection->addFieldToFilter('product_options', array('nlike' => '%;s:18:"adyen_subscription";s:4:"none"%'));
        $collection->addFieldToFilter('created_adyen_subscription', array('null' => true));
        $collection->addFieldToFilter('bao.agreement_id', array('notnull' => true)); // must have a billing agreements
        $collection->getSelect()->group('main_table.entity_id');

        $o = $p = $e = 0;
        foreach ($collection as $order) {
            try {
                $subscriptions = Mage::getModel('adyen_subscription/service_order')->createSubscription($order);

                foreach ($subscriptions as $subscription) {
                    /** @var Adyen_Subscription_Model_Subscription $subscription */
                    $message = Mage::helper('adyen_subscription')->__('Created a subscription (#%s) from order.', $subscription->getIncrementId());
                    Mage::helper('adyen_subscription')->logSubscriptionCron(sprintf("Created a subscription (#%s) from order (#%s)", $subscription->getIncrementId(), $order->getId()));
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

        $result = Mage::helper('adyen_subscription')->__(
            '%s orders processed, %s subscriptions created (%s errors)', $o, $p, $e
        );

        Mage::helper('adyen_subscription')->logSubscriptionCron($result);

        return $result;
    }


    /**
     * @return string
     */
    public function createQuotes()
    {
        Mage::helper('adyen_subscription')->logQuoteCron("Start quote cronjob");
        $subscriptionCollection = Mage::getResourceModel('adyen_subscription/subscription_collection');
        $subscriptionCollection->addScheduleQuoteFilter();

        if ($subscriptionCollection->count() <= 0) {
            Mage::helper('adyen_subscription')->logQuoteCron("For all subscriptions there is already a quote created");
            return '';
        }

        $timezone = new DateTimeZone(Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        ));
        $scheduleBefore = new DateTime('now', $timezone);
        $scheduleBefore->add(new DateInterval('P2W'));

        Mage::helper('adyen_subscription')->logQuoteCron(sprintf("Create quote if schedule is before %s", $scheduleBefore->format('Y-m-d H:i:s')));

        $successCount = 0;
        $failureCount = 0;
        foreach ($subscriptionCollection as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */

            if ($subscription->getScheduledAt()) {
                $timezone = new DateTimeZone(Mage::getStoreConfig(
                    Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
                ));
                $scheduleDate = new DateTime($subscription->getScheduledAt());
            } else {
                $scheduleDate = $subscription->calculateNextScheduleDate(true);
            }

            $subscription->setScheduledAt($scheduleDate->format('Y-m-d H:i:s'));

            Mage::helper('adyen_subscription')->logQuoteCron(sprintf("ScheduleDate of subscription (#%s) is %s", $subscription->getIncrementId(), $subscription->getScheduledAt()));

            if ($scheduleDate < $scheduleBefore) {
                try {
                    Mage::getSingleton('adyen_subscription/service_subscription')->createQuote($subscription);
                    $successCount++;
                } catch (Exception $e) {
                    Mage::helper('adyen_subscription')->logQuoteCron("Create quote error: " . $e->getMessage());
                    Adyen_Subscription_Exception::logException($e);
                    $failureCount++;
                }
            }
        }

        $result = Mage::helper('adyen_subscription')->__(
            'Quotes created, %s successful, %s failed', $successCount, $failureCount
        );

        Mage::helper('adyen_subscription')->logQuoteCron($result);

        return $result;
    }


    /**
     * @cron adyen_subscription_create_orders
     * @return string
     */
    public function createOrders()
    {
        Mage::helper('adyen_subscription')->logOrderCron("Start order cronjob");
        $subscriptionCollection = Mage::getResourceModel('adyen_subscription/subscription_collection');
        $subscriptionCollection->addPlaceOrderFilter();

        if ($subscriptionCollection->count() <= 0) {
            Mage::helper('adyen_subscription')->logOrderCron("There are no subscriptions that have quotes and a schedule date in the past");
            return '';
        }

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;
        foreach ($subscriptionCollection as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */

            if($subscription->getStatus() == Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR) {

                $retryFailedPayment = Mage::getStoreConfigFlag(
                    'adyen_subscription/subscription/retry_failed_payment'
                );

                // If setting 'retry payment failed' is off do not try to create order again
                if(!$retryFailedPayment) {
                    Mage::helper('adyen_subscription')->logOrderCron(sprintf("Subscription (#%s) is in status " . Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR . " so do not create order from quote because setting 'Retry failed payment' is set to no", $subscription->getId()));
                    $skippedCount++;
                    continue;
                }

                // setting that indicates how many times a failed payment is allowed to try again
                $numberOfFailedPaymentsAllowed = Mage::getStoreConfig(
                    'adyen_subscription/subscription/number_retry_failed_payment'
                );

                // setting that indicates what the time 
                $timeBetweenPaymentFailed = Mage::getStoreConfig(
                    'adyen_subscription/subscription/time_between_retry_failed_payment'
                );

                if($numberOfFailedPaymentsAllowed != "" || $timeBetweenPaymentFailed != "") {

                    // get history of payment errors
                    $subscriptionHistoryCollection = Mage::getResourceModel('adyen_subscription/subscription_history_collection');
                    $subscriptionHistoryCollection->getPaymentHistoryErrors($subscription);


                    // check how many times the payment is failed. And check if this is less then the chosen failed payments allowed settting
                    if($numberOfFailedPaymentsAllowed != "")
                    {
                        $numberOfPaymentErrors = $subscriptionHistoryCollection->getSize();
                        if($numberOfPaymentErrors > $numberOfFailedPaymentsAllowed) {
                            Mage::helper('adyen_subscription')->logOrderCron(sprintf("Subscription (#%s) is in status " . Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR . " you have configured to do not execute this if this happened more then %s times this is the %s time.", $subscription->getId(), $numberOfFailedPaymentsAllowed, $numberOfPaymentErrors));
                            $skippedCount++;
                            continue;
                        }
                    }

                    if($timeBetweenPaymentFailed != "")
                    {
                        $now = new DateTime('now'); // -2 hours
                        $lastPaymentErrorDate =  $subscriptionHistoryCollection->getLastItem()->getDate(); // -2 hours

                        $nextTry = new DateTime($lastPaymentErrorDate);
                        $nextTry->add(new DateInterval('PT' . $timeBetweenPaymentFailed . 'H'));

                        if($now < $nextTry) {
                            // do not update
                            Mage::helper('adyen_subscription')->logOrderCron(sprintf("Subscription (#%s) is in status " . Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR . " you have configured that the next time is %s hours after last payment error this is not yet the case. Next try will be at %s", $subscription->getId(), $timeBetweenPaymentFailed, $nextTry->format('Y-m-d H:i:s')));
                            $skippedCount++;
                            continue;

                        }
                    }
                }
            }

            try {
                $quote = $subscription->getActiveQuote();
                if (! $quote) {
                    Mage::helper('adyen_subscription')->logOrderCron("Can\'t create order: No quote created yet.");
                    Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
                }

                Mage::getSingleton('adyen_subscription/service_quote')->createOrder($subscription->getActiveQuote(), $subscription);
                $successCount++;
            } catch (Exception $e) {
                Adyen_Subscription_Exception::logException($e);
                $failureCount++;
            }
        }

        $result = Mage::helper('adyen_subscription')->__(
            'Quotes created, %s successful, %s failed, %s skipped', $successCount, $failureCount, $skippedCount
        );

        Mage::helper('adyen_subscription')->logOrderCron($result);

        return $result;
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

                Mage::helper('adyen_subscription')->logSubscriptionCron(
                    sprintf("Updated prices for subscription #%s, item %s, from %s (%s) to %s (%s)",
                        $subscription->getIncrementId(),
                        $subscriptionItem->getSku(),
                        $subscriptionItem->getPrice(),
                        $subscriptionItem->getPriceInclTax(),
                        $price,
                        $priceInclTax
                    )
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