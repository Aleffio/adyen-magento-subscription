<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

class Adyen_Subscription_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function logSubscriptionCron($message)
    {
        if ($this->_getInfoLoggingEnabled()) {
            $this->log($message, "adyen_subscription_cron");
        }
    }

    public function logQuoteCron($message)
    {
        if ($this->_getInfoLoggingEnabled()) {
            $this->log($message, "adyen_quote_cron");
        }
    }

    public function logOrderCron($message)
    {
        if ($this->_getInfoLoggingEnabled()) {
            $this->log($message, "adyen_order_cron");
        }
    }

    public function log($message, $filename)
    {
        if(Mage::getStoreConfigFlag(
            'adyen_subscription/subscription/debug',
            Mage::app()->getStore()
        ))
        {
            Mage::log($message, Zend_Log::DEBUG, "$filename.log", true);
        }
    }

    protected function _getInfoLoggingEnabled()
    {
        return Mage::getStoreConfigFlag(
            'adyen_subscription/advanced/enable_logging',
            Mage::app()->getStore()
        );
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getAdminOrderUrlHtml(Mage_Sales_Model_Order $order)
    {
        return sprintf('<a href="%s">#%s</a>',
            Mage::helper('adminhtml')->getUrl('*/sales_order/view', ['order_id' => $order->getId()]),
            $order->getIncrementId());
    }
}
