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
            $scheduleDate = $profile->calculateNextScheduleDate(true);
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
            'Quotes created, %s success full, %s failed', $successCount, $failureCount
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
            'Quotes created, %s success full, %s failed', $successCount, $failureCount
        );
    }


    public function convertOrderToProfile(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $observer->getOrder();
        $profiles = Mage::getSingleton('ho_recurring/service_order')->createProfile($order);

        foreach ($profiles as $profile) {
            $message = Mage::helper('ho_recurring')->__("Created a recurring profile (#%s) from order.", $profile->getId());
            $order->addStatusHistoryComment($message);
        }
    }


    public function addAdminhtmlSalesOrderCreateHandles(Varien_Event_Observer $observer)
    {
        $profileId = Mage::app()->getRequest()->getParam('profile');
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            return;
        }

        Mage::register('current_profile', $profile);
        Mage::app()->getLayout()->getUpdate()->addHandle('ho_recurring_active_quote_edit');
    }
}
