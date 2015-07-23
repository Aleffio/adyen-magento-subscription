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

        if ($session->getData('subscription_quote_initialized')) {
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
}
