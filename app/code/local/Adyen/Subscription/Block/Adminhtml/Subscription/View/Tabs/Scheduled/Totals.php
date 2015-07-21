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

class Adyen_Subscription_Block_Adminhtml_Profile_View_Tabs_Scheduled_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Create_Totals
{
    /**
     * Retrieve quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getProfile()->getActiveQuote();
    }

    /**
     * @return Adyen_Subscription_Model_Profile_Quote
     */
    public function getQuoteAdditional()
    {
        return $this->getProfile()->getActiveQuoteAdditional();
    }

    /**
     * Retrieve customer model object
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->getProfile()->getCustomer();
    }

    /**
     * Retrieve customer identifier
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getProfile()->getCustomerId();
    }

    /**
     * Retrieve store model object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->getProfile()->getStoreId());
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getProfile()->getStoreId();
    }

    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('adyen_subscription');
    }

    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach($this->getTotals() as $total) {
            /** @var Mage_Sales_Model_Quote_Address_Total $total */
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            if (! $total->getValue()) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }
}
