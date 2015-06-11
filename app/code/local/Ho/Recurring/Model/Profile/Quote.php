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
 * Class Ho_Recurring_Model_Profile_Quote
 *
 * @method int getProfileId()
 * @method Ho_Recurring_Model_Profile_Quote setProfileId(int $value)
 * @method int getQuoteId()
 * @method Ho_Recurring_Model_Profile_Quote setQuoteId(int $value)
 * @method int getEntityId()
 * @method Ho_Recurring_Model_Profile_Quote setEntityId(int $value)
 * @method int getOrderId()
 * @method Ho_Recurring_Model_Profile_Quote setOrderId(int $value)
 */
class Ho_Recurring_Model_Profile_Quote extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ho_recurring/profile_quote');
    }

    /**
     * @param Ho_Recurring_Model_Profile $profile
     * @return Ho_Recurring_Model_Profile_Quote
     */
    public function setProfile(Ho_Recurring_Model_Profile $profile)
    {
        $this->setData('_profile', $profile);
        $this->setProfileId($profile->getId());
        return $this;
    }


    /**
     * @return Ho_Recurring_Model_Profile
     */
    public function getProfile()
    {
        if (! $this->hasData('_profile')) {
            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('ho_recurring/profile')
                ->load($this->getProfileId());

            $this->setData('_profile', $quote);
        }

        return $this->getData('_profile');
    }


    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return Ho_Recurring_Model_Profile_Quote
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->setData('_quote', $quote);
        $this->setQuoteId($quote->getId());
        return $this;
    }


    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (! $this->hasData('_quote')) {
            // Note: The quote won't load if we don't set the store ID
            $quote = Mage::getModel('sales/quote')
                ->setStoreId($this->getProfile()->getStoreId())
                ->load($this->getQuoteId());

            $this->setData('_quote', $quote);
        }

        return $this->getData('_quote');
    }



    /**
     * @param Mage_Sales_Model_Order $order
     * @return Ho_Recurring_Model_Profile_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->setData('_order', $order);
        $this->setOrderId($order->getId());
        return $this;
    }


    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (! $this->hasData('_order')) {
            // Note: The order won't load if we don't set the store ID
            $order = Mage::getModel('sales/order')
                ->setStoreId($this->getProfile()->getStoreId())
                ->load($this->getOrderId());

            $this->setData('_order', $order);
        }

        return $this->getData('_order');
    }
}
