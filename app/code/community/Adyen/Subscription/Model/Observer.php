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

class Adyen_Subscription_Model_Observer extends Mage_Core_Model_Abstract
{
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
                $scheduleDate = new DateTime($subscription->getScheduledAt(), $timezone);
            }
            else {
                $scheduleDate = $subscription->calculateNextScheduleDate(true);
            }

            $subscription->setScheduledAt($scheduleDate->format('Y-m-d H:i:s'));

            Mage::helper('adyen_subscription')->logQuoteCron(sprintf("ScheduleDate of subscription (#%s) is %s", $subscription->getId(), $subscription->getScheduledAt()));

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
        foreach ($subscriptionCollection as $subscription) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */

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
            'Quotes created, %s successful, %s failed', $successCount, $failureCount
        );

        Mage::helper('adyen_subscription')->logOrderCron($result);

        return $result;
    }

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
                    Mage::helper('adyen_subscription')->logSubscriptionCron(sprintf("Created a subscription (#%s) from order (#%s)", $subscription->getId(), $order->getId()));
                    $order->addStatusHistoryComment($message);
                    //TODO: FLag order that subscription is created so it will not recreate it when you delete the subscription and add it to filter so it is not looped to it again that will be better for performance
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
     * @param Varien_Event_Observer $observer
     * @hook controller_action_layout_load_before
     */
    public function addAdminhtmlSalesOrderCreateHandles(Varien_Event_Observer $observer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if (! $observer->getAction() instanceof Mage_Adminhtml_Sales_Order_CreateController) {
            return;
        }

        $subscriptionId = Mage::app()->getRequest()->getParam('subscription');
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            return;
        }

        Mage::register('current_subscription', $subscription);
        Mage::app()->getLayout()->getUpdate()->addHandle('adyen_subscription_active_quote_edit');
    }

    /**
     * Save additional (subscription) product options (added in addSubscriptionProductSubscriptionToQuote)
     * from quote items to order items
     *
     * @event sales_convert_quote_item_to_order_item
     * @param Varien_Event_Observer $observer
     */
    public function addSubscriptionProductSubscriptionToOrder(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Item $quoteItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $quoteItem = $observer->getItem();
        /** @var Mage_Sales_Model_Order_Item $orderItem */
        /** @noinspection PhpUndefinedMethodInspection */
        $orderItem = $observer->getOrderItem();

        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $options = $orderItem->getProductOptions();

            $options['additional_options'] = unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
    }

    /**
     * Join subscription ID to sales order grid
     *
     * @event sales_order_grid_collection_load_before
     * @param Varien_Event_Observer $observer
     */
    public function beforeOrderCollectionLoad($observer)
    {
        /** @var Mage_Sales_Model_Resource_Order_Collection $collection */
        /** @noinspection PhpUndefinedMethodInspection */
        $collection = $observer->getOrderGridCollection();

        $union = $collection->getSelect()->getPart(Zend_Db_Select::UNION);
        $resource = $collection->getResource();

        if (count($union) > 1) {
            foreach ($union as $unionSelect) {
                list($target, $type) = $unionSelect;
                $target->joinLeft(
                    array('subscription' => $resource->getTable('adyen_subscription/subscription')),
                    '`main_table`.`entity_id` = `subscription`.`order_id`',
                    array('created_subscription_id' => 'group_concat(subscription.entity_id)')
                );
                $target->group('main_table.entity_id');
            }
        }
        else {
            $collection->getSelect()->joinLeft(
                array('subscription' => $resource->getTable('adyen_subscription/subscription')),
                '`main_table`.`entity_id` = `subscription`.`order_id`',
                array('created_subscription_id' => 'group_concat(subscription.entity_id)')
            );
            $collection->getSelect()->group('main_table.entity_id');
        }
    }

    /**
     * Add subscription IDs column to order grid
     *
     * @event adyen_subscription_add_sales_order_grid_column
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addGridColumn(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (! $block instanceof Mage_Adminhtml_Block_Sales_Order_Grid && !$block instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_Orders) {
            return $this;
        }

        $block->addColumnAfter('created_subscription_id', array(
            'header'        => Mage::helper('sales')->__('Created Subscription ID'),
            'index'         => 'created_subscription_id',
            'filter_index'  => 'subscription.entity_id',
            'type'          => 'text',
            'width'         => '100px',
        ), 'status');

        return $this;
    }

    /**
     * Set the right amount of qty on the order items when placing an order.
     * The ordered qty is multiplied by the 'qty in subscription' amount of the
     * selected subscription.
     *
     * @event sales_order_place_before
     * @param Varien_Event_Observer $observer
     */
    public function calculateItemQty(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        foreach ($order->getItemsCollection() as $orderItem) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            $additionalOptions = $orderItem->getProductOptionByCode('additional_options');

            if (! is_array($additionalOptions)) continue;

            $subscriptionOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription') {
                    $subscriptionOptions = $additionalOption;
                    break;
                }
            }

            if (! $subscriptionOptions || $orderItem->getParentItemId()) continue;

            $productSubscription = Mage::getModel('adyen_subscription/product_subscription')->load($subscriptionOptions['option_value']);

            $subscriptionQty = $productSubscription->getQty();
            if ($subscriptionQty > 1) {
                $qty = $orderItem->getQtyOrdered() * $subscriptionQty;

                $orderItem = $this->_correctPrices($orderItem, $orderItem->getQtyOrdered(), $qty);
                $orderItem->setQtyOrdered($qty);
                $orderItem->save();

                foreach ($orderItem->getChildrenItems() as $childItem) {
                    /** @var Mage_Sales_Model_Order_Item $childItem */
                    $childItemQty = $childItem->getQtyOrdered() * $subscriptionQty;

                    $childItem = $this->_correctPrices($childItem, $childItem->getQtyOrdered(), $childItemQty);
                    $childItem->setQtyOrdered($childItemQty);
                    $childItem->save();
                }
            }
        }
    }

    /**
     * Set correct item prices ((original price / new qty) * old qty)
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param int $oldQty
     * @param int $newQty
     * @return Mage_Sales_Model_Order_Item
     */
    protected function _correctPrices($orderItem, $oldQty, $newQty)
    {
        $orderItem->setPrice(($orderItem->getPrice() / $newQty) * $oldQty);
        $orderItem->setBasePrice(($orderItem->getBasePrice() / $newQty) * $oldQty);
        $orderItem->setOriginalPrice(($orderItem->getOriginalPrice() / $newQty) * $oldQty);
        $orderItem->setBaseOriginalPrice(($orderItem->getBaseOriginalPrice() / $newQty) * $oldQty);

        $orderItem->setPriceInclTax(($orderItem->getPriceInclTax() / $newQty) * $oldQty);
        $orderItem->setBasePriceInclTax(($orderItem->getPriceInclTax() / $newQty) * $oldQty);

        return $orderItem;
    }

    /**
     * Set the right amount of qty on the order items when reordering.
     * The qty of the ordered items is divided by the 'qty in subscription'
     * amount of the selected product subscription.
     *
     * @event create_order_session_quote_initialized
     * @param Varien_Event_Observer $observer
     */
    public function calculateItemQtyReorder(Varien_Event_Observer $observer)
    {
        $subscriptionQuote = false;

        /** @var Mage_Core_Model_Session $session */
        $session = $observer->getSessionQuote();

        if ($session->getData('subscription_quote_initialized') || ! $session->getReordered()) {
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $session->getQuote();

        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');

            if (! $additionalOptions || $quoteItem->getParentItemId()) continue;

            $additionalOptions = unserialize($additionalOptions->getValue());

            $subscriptionOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription') {
                    $subscriptionOptions = $additionalOption;
                    break;
                }
            }

            if (! $subscriptionOptions) continue;

            $productSubscription = Mage::getModel('adyen_subscription/product_subscription')->load($subscriptionOptions['option_value']);

            $subscriptionQty = $productSubscription->getQty();
            if ($subscriptionQty > 1) {
                $qty = $quoteItem->getQty() / $subscriptionQty;

                $quoteItem->setQty($qty);
                $quoteItem->save();

                $subscriptionQuote = true;
            }
        }

        if ($subscriptionQuote) {
            $quote->collectTotals();
            $session->setData('subscription_quote_initialized', true);
        }
    }


    /**
     * The BillingAgreement of an subscription can change for IDEAL and Sofort
     * When you do a recurring transaction for Ideal it will transform the payment to a SEPA payment
     * This will resolve in a new recurring_detail_reference that you need to use for future payments
     * so update the subscription with this new reference number
     * @param Varien_Event_Observer $observer
     */
    public function updateBillingAgreementInSubscription(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Model_Order $order */
        $order = $observer->getOrder();

        /** @var Varien_OBject $response */
        $response = $observer->getAdyenResponse();

        $eventCode = trim($response->getData('eventCode'));
        if($eventCode == Adyen_Payment_Model_Event::ADYEN_EVENT_RECURRING_CONTRACT) {

            // get billingAgreement of the order, an order is always connected to one agreement
            $billingAgreementId = $this->_getBillingAgreementId($order);

            if ($billingAgreementId) {
                // check if order has subscription(s)

                // subscription_order
                $subscriptionOrders = Mage::getModel('adyen_subscription/subscription_order')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());

                if ($subscriptionOrders->count() <= 0) {
                    return '';
                }

                // If the billingagreementId of the subscription does not match the new billingagreementId change the billingAgreementId to this new value
                foreach($subscriptionOrders as $subscriptionOrders) {

                    $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionOrders->getSubscriptionId());
                    $billingAgreementIdOfSubs = $subscription->getBillingAgreementId();
                    if($billingAgreementIdOfSubs != $billingAgreementId) {
                        try {
                            $subscription->setBillingAgreementId($billingAgreementId);
                            $subscription->save();
                        } catch(Exception $e) {
                            new Adyen_Subscription_Exception('Could not update subscrription '.$e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _getBillingAgreementId(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();

        $select->from($resource->getTableName('sales/billing_agreement_order'));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('agreement_id');
        $select->where('order_id = ?', $order->getId());
        $select->order(array('agreement_id DESC')); // important to get last agreement_id


        $billingAgreementId = $connection->fetchOne($select);
        if (! $billingAgreementId) {
            Adyen_Subscription_Exception::logException(
                new Adyen_Subscription_Exception('Could not find billing agreement for order '.$order->getIncrementId())
            );
            return null;
        }

        return $billingAgreementId;
    }


    /**
     * Check if billing agreement is not linked to a subscription. If this is the case return an exception
     * @param Varien_Event_Observer $observer
     */
    public function updateBillingAgreementStatus(Varien_Event_Observer $observer) {

        $agreement = $observer->getAgreement();
        $agreementId = $agreement->getId();

        $subscriptionCollection = Mage::getModel('adyen_subscription/subscription')
            ->getCollection()
            ->addFieldToFilter('billing_agreement_id', $agreementId);

        if ($subscriptionCollection->count() > 0) {
            Mage::throwException(Mage::helper('adyen_subscription')->__(
                'You cannot cancel this billing agreement because it is used for a subscription.'
            ));
        }
    }
}
