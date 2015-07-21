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

class Adyen_Subscription_Model_Resource_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('adyen_subscription/profile', 'entity_id');
    }

    public function loadByOrder(
        Adyen_Subscription_Model_Profile $object,
        Mage_Sales_Model_Order $order
    ) {
        $orderSelect = Mage::getResourceModel('adyen_subscription/profile_order_collection')
            ->addFieldToFilter('order_id', $order->getId())
            ->getSelect();

        $orderSelect->reset($orderSelect::COLUMNS);
        $orderSelect->columns('profile_id');

        $profileId = $this->_getConnection('read')->fetchOne($orderSelect);

        $this->load($object, $profileId);

        return $this;
    }
}
