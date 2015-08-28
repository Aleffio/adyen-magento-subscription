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

class Adyen_Subscription_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function logSubscriptionCron($message)
    {
        $this->log($message, "adyen_subscription_cron");
    }

    public function logQuoteCron($message)
    {
        $this->log($message, "adyen_quote_cron");
    }

    public function logOrderCron($message)
    {
        $this->log($message, "adyen_order_cron");
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

}
