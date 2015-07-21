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

class Adyen_Subscription_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SUBSCRIPTION_CANCEL_REASONS   = 'adyen_subscription/subscription/cancel_reasons';

    /**
     * @return array
     */
    public function getCancelReasons()
    {
        $config = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_CANCEL_REASONS);

        return $config ? unserialize($config) : array();
    }
}
