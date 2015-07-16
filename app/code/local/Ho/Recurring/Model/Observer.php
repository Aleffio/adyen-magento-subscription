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

class Ho_Recurring_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @return string
     */
    public function createQuotes()
    {
        $profileCollection = Mage::getResourceModel('ho_recurring/profile_collection');
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
            /** @var Ho_Recurring_Model_Profile $profile */

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
                    Mage::getSingleton('ho_recurring/service_profile')->createQuote($profile);
                    $successCount++;
                } catch (Exception $e) {
                    Ho_Recurring_Exception::logException($e);
                    $failureCount++;
                }
            }
        }

        return Mage::helper('ho_recurring')->__(
            'Quotes created, %s successful, %s failed', $successCount, $failureCount
        );
    }

    /**
     * @return string
     */
    public function createOrders()
    {
        $profileCollection = Mage::getResourceModel('ho_recurring/profile_collection');
        $profileCollection->addPlaceOrderFilter();

        if ($profileCollection->count() <= 0) {
            return '';
        }

        $successCount = 0;
        $failureCount = 0;
        foreach ($profileCollection as $profile) {
            /** @var Ho_Recurring_Model_Profile $profile */

            try {
                $quote = $profile->getActiveQuote();
                if (! $quote) {
                    Ho_Recurring_Exception::throwException('Can\'t create order: No quote created yet.');
                }

                Mage::getSingleton('ho_recurring/service_quote')->createOrder($profile->getActiveQuote(), $profile);
                $successCount++;
            } catch (Exception $e) {
                Ho_Recurring_Exception::logException($e);
                $failureCount++;
            }
        }

        return Mage::helper('ho_recurring')->__(
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
            array('recurring_profile' => $resource->getTable('ho_recurring/profile')),
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
        $collection->addFieldToFilter('product_options', array('nlike' => '%;s:20:"ho_recurring_profile";s:4:"none"%'));

        $collection->getSelect()->group('main_table.entity_id');

        $o = $p = $e = 0;
        foreach ($collection as $order) {
            try {
                $profiles = Mage::getModel('ho_recurring/service_order')->createProfile($order);

                foreach ($profiles as $profile) {
                    /** @var Ho_Recurring_Model_Profile $profile */
                    $message = Mage::helper('ho_recurring')->__('Created a recurring profile (#%s) from order.', $profile->getId());
                    $order->addStatusHistoryComment($message);
                    $order->save();
                    $p++;
                }
                $o++;
            }
            catch (Exception $exception) {
                $e++;
                Ho_Recurring_Exception::logException($exception);
            }
        }

        return Mage::helper('ho_recurring')->__(
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
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            return;
        }

        Mage::register('current_profile', $profile);
        Mage::app()->getLayout()->getUpdate()->addHandle('ho_recurring_active_quote_edit');
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
                    array('recurring_profile' => 'ho_recurring_profile'),
                    '`main_table`.`entity_id` = `recurring_profile`.`order_id`',
                    array('created_recurring_profile_id' => 'group_concat(recurring_profile.entity_id)')
                );
                $target->group('main_table.entity_id');
            }
        }
        else {
            $collection->getSelect()->joinLeft(
                array('recurring_profile' => 'ho_recurring_profile'),
                '`main_table`.`entity_id` = `recurring_profile`.`order_id`',
                array('created_recurring_profile_id' => 'group_concat(recurring_profile.entity_id)')
            );
            $collection->getSelect()->group('main_table.entity_id');
        }
    }

    /**
     * Add recurring profile IDs column to order grid
     *
     * @event ho_recurring_add_sales_order_grid_column
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
                if ($additionalOption['code'] == 'ho_recurring_profile') {
                    $recurringOptions = $additionalOption;
                    break;
                }
            }

            if (! $recurringOptions) continue;

            $productProfile = Mage::getModel('ho_recurring/product_profile')->load($recurringOptions['option_value']);

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

}
