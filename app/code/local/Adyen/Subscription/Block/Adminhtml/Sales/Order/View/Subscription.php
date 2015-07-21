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

/**
 * Class Adyen_Subscription_Block_Adminhtml_Sales_Order_View_Recurring
 * @method $this setProfile(Adyen_Subscription_Model_Profile $profile)
 * @method Adyen_Subscription_Model_Profile getProfile()
 * @see admin/subscription/sales/order/view/recurring.phtml
 */
class Adyen_Subscription_Block_Adminhtml_Sales_Order_View_Subscription
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * @return mixed
     */
    public function getOrderInfoData()
    {
        return $this->getParentBlock()->getOrderInfoData();
    }

    protected function _toHtml()
    {
        $order = $this->getOrder();
        $profile = Mage::getModel('adyen_subscription/profile')->loadByOrder($order);

        if (! $profile->getId()) {
            return $this->getChildHtml();
        }

        $this->setProfile($profile);
        return parent::_toHtml();
    }

    /**
     * @return Adyen_Subscription_Model_Profile_Order
     */
    public function getProfileOrderAdditionalInfo()
    {
        return $this->getProfile()->getOrderAdditional($this->getOrder());
    }


    /**
     * @return Adyen_Subscription_Model_Profile_Quote|null
     */
    public function getProfileQuoteAdditionalInfo()
    {
        $quoteAdditional = Mage::getModel('adyen_subscription/profile_quote')
            ->load($this->getOrder()->getQuoteId(), 'quote_id');

        return $quoteAdditional->getId() ? $quoteAdditional : null;
    }
}
