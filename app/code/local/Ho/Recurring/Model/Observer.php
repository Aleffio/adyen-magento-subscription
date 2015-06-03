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

            $scheduleDate = $profile->getScheduledAt() ?: $profile->calculateNextScheduleDate(true);
            $profile->setScheduledAt($scheduleDate->format('Y-m-d H:i:s'));
            /** @var Ho_Recurring_Model_Profile $profile */

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
     * @param Varien_Event_Observer $observer
     */
    public function convertOrderToProfile(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $observer->getOrder();
        $profiles = Mage::getSingleton('ho_recurring/service_order')->createProfile($order);

        foreach ($profiles as $profile) {
            /** @var Ho_Recurring_Model_Profile $profile */
            $message = Mage::helper('ho_recurring')->__("Created a recurring profile (#%s) from order.", $profile->getId());
            $order->addStatusHistoryComment($message);
        }
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
                    array('created_recurring_profile_id' => 'entity_id')
                );
            }
        }
        else {
            $collection->getSelect()->joinLeft(
                array('recurring_profile' => 'ho_recurring_profile'),
                '`main_table`.`entity_id` = `recurring_profile`.`order_id`',
                array('created_recurring_profile_id' => 'entity_id')
            );
        }
    }
}
