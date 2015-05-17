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

class Ho_Recurring_Model_Service_Quote
{

    /**
     * @param Mage_Sales_Model_Quote     $quote
     * @param Ho_Recurring_Model_Profile $profile
     *
     * @return Mage_Sales_Model_Order
     * @throws Ho_Recurring_Exception|Exception
     */
    public function createOrder(
        Mage_Sales_Model_Quote $quote,
        Ho_Recurring_Model_Profile $profile
    ) {
        try {
            if (! $profile->canCreateOrder()) {
                Ho_Recurring_Exception::throwException('Can not create quote from profile');
            }

            $quote->collectTotals();
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();
            $order = $service->getOrder();

            $orderAdditional = $profile->getOrderAdditional($service->getOrder(), true)->save();
            $quoteAdditional = $profile->getActiveQuoteAdditional()->setOrder($order)->save();

            $profile->setErrorMessage(null);
            if ($profile->getStatus() == $profile::STATUS_ORDER_ERROR) {
                $profile->setStatus($profile::STATUS_ACTIVE);
            }

            Mage::getModel('core/resource_transaction')
                ->addObject($profile)
                ->addObject($orderAdditional)
                ->addObject($quoteAdditional)
                ->save();

            return $service->getOrder();
        } catch (Exception $e) {
            $profile->setStatus($profile::STATUS_ORDER_ERROR);
            $profile->setErrorMessage($e->getMessage());
            $profile->save();
            throw $e;
        }
    }
}
