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

/**
 * Class Ho_Recurring_Model_Profile
 *
 * @method int getOrderId()
 * @method setOrderId(int $value)
 * @method int getBillingAgreementId()
 * @method setBillingAgreementId(int $value)
 */
class Ho_Recurring_Model_Profile extends Mage_Core_Model_Abstract
{
    protected function _construct ()
    {
        $this->_init('ho_recurring/profile');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function createQuote()
    {
        $quote = Mage::getModel('ho_recurring/service_profile')->createQuote($this);

        Mage::getModel('ho_recurring/profile_quote')
            ->setProfileId($this->getId())
            ->setQuoteId($quote->getId())
            ->save();

        return $quote;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function createOrder()
    {
        $quote = $this->createQuote();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        Mage::getModel('ho_recurring/profile_order')
            ->setProfileId($this->getId())
            ->setOrderId($order->getId())
            ->save();

        return $order;
    }

    /**
     * @return Ho_Recurring_Model_Resource_Profile_Item_Collection
     */
    public function getItems()
    {
        return Mage::getModel('ho_recurring/profile_item')
            ->getCollection()
            ->addFieldToFilter('profile_id', $this->getId());
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOriginalOrder()
    {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }
}
