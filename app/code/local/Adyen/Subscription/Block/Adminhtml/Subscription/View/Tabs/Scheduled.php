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

class Adyen_Subscription_Block_Adminhtml_Subscription_View_Tabs_Scheduled
    extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return array
     */
    public function getAllVisibleItems()
    {
        return $this->getSubscription()->getActiveQuote()->getAllVisibleItems();
    }

    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adyen_subscription')->__('Scheduled Order');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adyen_subscription')->__('Scheduled Order');
    }

    /**
     * Don't show tab if there is no quote
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getSubscription()->getActiveQuote();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
