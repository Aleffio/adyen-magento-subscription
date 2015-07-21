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
        $profileCollection = Mage::getResourceModel('adyen_subscription/profile_collection');
        $profileCollection->addScheduleQuoteFilter();

        if ($profileCollection->count() <= 0) {
            return '';
        }

        $timezone = new DateTimeZone(Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        ));
        $scheduleBefore = new DateTime('now', $timezone);
        $scheduleBefore->add(new DateInterval('P2W'));

        $successCount = 0;
        $failureCount = 0;
        foreach ($profileCollection as $profile) {
            /** @var Adyen_Subscription_Model_Profile $profile */

            if ($profile->getScheduledAt()) {
                $timezone = new DateTimeZone(Mage::getStoreConfig(
                    Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
                ));
                $scheduleDate = new DateTime($profile->getScheduledAt(), $timezone);
            }
            else {
                $scheduleDate = $profile->calculateNextScheduleDate(true);
            }

            $profile->setScheduledAt($scheduleDate->format('Y-m-d H:i:s'));

            if ($scheduleDate < $scheduleBefore) {
                try {
                    Mage::getSingleton('adyen_subscription/service_profile')->createQuote($profile);
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
        $profileCollection = Mage::getResourceModel('adyen_subscription/profile_collection');
        $profileCollection->addPlaceOrderFilter();

        if ($profileCollection->count() <= 0) {
            return '';
        }

        $successCount = 0;
        $failureCount = 0;
        foreach ($profileCollection as $profile) {
            /** @var Adyen_Subscription_Model_Profile $profile */

            try {
                $quote = $profile->getActiveQuote();
                if (! $quote) {
                    Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
                }

                Mage::getSingleton('adyen_subscription/service_quote')->createOrder($profile->getActiveQuote(), $profile);
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
    public function createProfiles()
    {
        $collection = Mage::getModel('sales/order')->getCollection();

        $resource = $collection->getResource();

        $collection->getSelect()->joinLeft(
            array('recurring_profile' => $resource->getTable('adyen_subscription/profile')),
            'main_table.entity_id = recurring_profile.order_id',
            array('created_recurring_profile_id' => 'entity_id')
        );
        $collection->getSelect()->joinLeft(
            array('oi' => $resource->getTable('sales/order_item')),
            'main_table.entity_id = oi.order_id',
            array('oi.item_id', 'oi.parent_item_id', 'oi.product_options')
        );

        $collection->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $collection->addFieldToFilter('recurring_profile.entity_id', array('null' => true));
        $collection->addFieldToFilter('parent_item_id', array('null' => true));
        $collection->addFieldToFilter('product_options', array('nlike' => '%;s:20:"adyen_subscription_profile";s:4:"none"%'));

        $collection->getSelect()->group('main_table.entity_id');

        $o = $p = $e = 0;
        foreach ($collection as $order) {
            try {
                $profiles = Mage::getModel('adyen_subscription/service_order')->createProfile($order);

                foreach ($profiles as $profile) {
                    /** @var Adyen_Subscription_Model_Profile $profile */
                    $message = Mage::helper('adyen_subscription')->__('Created a recurring profile (#%s) from order.', $profile->getId());
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
            '%s orders processed, %s profiles created (%s errors)', $o, $p, $e
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

        $profileId = Mage::app()->getRequest()->getParam('profile');
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            return;
        }

        Mage::register('current_profile', $profile);
        Mage::app()->getLayout()->getUpdate()->addHandle('adyen_subscription_active_quote_edit');
    }

    /**
     * Save additional (recurring) product options (added in addRecurringProductProfileToQuote)
     * from quote items to order items
     *
     * @event sales_convert_quote_item_to_order_item
     * @param Varien_Event_Observer $observer
     */
    public function addRecurringProductProfileToOrder(Varien_Event_Observer $observer)
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
     * Join recurring profile ID to sales order grid
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

        if (count($union) > 1) {
            foreach ($union as $unionSelect) {
                list($target, $type) = $unionSelect;
                $target->joinLeft(
                    array('recurring_profile' => 'adyen_subscription_profile'),
                    '`main_table`.`entity_id` = `recurring_profile`.`order_id`',
                    array('created_recurring_profile_id' => 'group_concat(recurring_profile.entity_id)')
                );
                $target->group('main_table.entity_id');
            }
        }
        else {
            $collection->getSelect()->joinLeft(
                array('recurring_profile' => 'adyen_subscription_profile'),
                '`main_table`.`entity_id` = `recurring_profile`.`order_id`',
                array('created_recurring_profile_id' => 'group_concat(recurring_profile.entity_id)')
            );
            $collection->getSelect()->group('main_table.entity_id');
        }
    }

    /**
     * Add recurring profile IDs column to order grid
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

        $block->addColumnAfter('created_recurring_profile_id', array(
            'header'        => Mage::helper('sales')->__('Created Recurring Profile ID'),
            'index'         => 'created_recurring_profile_id',
            'filter_index'  => 'recurring_profile.entity_id',
            'type'          => 'text',
            'width'         => '100px',
        ), 'status');

        return $this;
    }

    /**
     * Set the right amount of qty on the order items when placing an order.
     * The ordered qty is multiplied by the 'qty in profile' amount of the
     * selected recurring product profile.
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

            $recurringOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription_profile') {
                    $recurringOptions = $additionalOption;
                    break;
                }
            }

            if (! $recurringOptions || $orderItem->getParentItemId()) continue;

            $productProfile = Mage::getModel('adyen_subscription/product_profile')->load($recurringOptions['option_value']);

            $profileQty = $productProfile->getQty();
            if ($profileQty > 1) {
                $qty = $orderItem->getQtyOrdered() * $profileQty;

                $orderItem->setQtyOrdered($qty);
                $orderItem->save();

                foreach ($orderItem->getChildrenItems() as $childItem) {
                    /** @var Mage_Sales_Model_Order_Item $childItem */
                    $childItemQty = $childItem->getQtyOrdered() * $profileQty;

                    $childItem->setQtyOrdered($childItemQty);
                    $childItem->save();
                }
            }
        }
    }

    /**
     * Set the right amount of qty on the order items when reordering.
     * The qty of the ordered items is divided by the 'qty in profile'
     * amount of the selected recurring product profile.
     *
     * @event create_order_session_quote_initialized
     * @param Varien_Event_Observer $observer
     */
    public function calculateItemQtyReorder(Varien_Event_Observer $observer)
    {
        $recurringQuote = false;

        /** @var Mage_Core_Model_Session $session */
        $session = $observer->getSessionQuote();

        if ($session->getData('recurring_quote_initialized')) {
            return;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $session->getQuote();

        foreach ($quote->getItemsCollection() as $quoteItem) {
            /** @var Mage_Sales_Model_Quote_Item $quoteItem */
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');

            if (! $additionalOptions || $quoteItem->getParentItemId()) continue;

            $additionalOptions = unserialize($additionalOptions->getValue());

            $recurringOptions = false;
            foreach ($additionalOptions as $additionalOption) {
                if ($additionalOption['code'] == 'adyen_subscription_profile') {
                    $recurringOptions = $additionalOption;
                    break;
                }
            }

            if (! $recurringOptions) continue;

            $productProfile = Mage::getModel('adyen_subscription/product_profile')->load($recurringOptions['option_value']);

            $profileQty = $productProfile->getQty();
            if ($profileQty > 1) {
                $qty = $quoteItem->getQty() / $profileQty;

                $quoteItem->setQty($qty);
                $quoteItem->save();

                $recurringQuote = true;
            }
        }

        if ($recurringQuote) {
            $quote->collectTotals();
            $session->setData('recurring_quote_initialized', true);
        }
    }
}
