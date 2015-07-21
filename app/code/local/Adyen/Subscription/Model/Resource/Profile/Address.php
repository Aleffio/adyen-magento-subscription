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

class Adyen_Subscription_Model_Resource_Subscription_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('adyen_subscription/subscription_address', 'item_id');
    }

    /**
     * @param Adyen_Subscription_Model_Subscription_Address $object
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @param int $type
     * @return $this
     */
    public function loadBySubscription(
        Adyen_Subscription_Model_Subscription_Address $object,
        Adyen_Subscription_Model_Subscription $subscription,
        $type
    ) {
        $select = Mage::getResourceModel('adyen_subscription/subscription_address_collection')
            ->addFieldToFilter('subscription_id', $subscription->getId())
            ->addFieldToFilter('type', $type)
            ->getSelect();

        $select->reset($select::COLUMNS);
        $select->columns('item_id');

        $addressId = $this->_getConnection('read')->fetchOne($select);

        $this->load($object, $addressId);

        return $this;
    }
}
