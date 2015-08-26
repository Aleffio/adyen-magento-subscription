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

class Adyen_Subscription_Model_Resource_Subscription_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/subscription_history');
    }

    public function getPaymentHistoryErrors(Adyen_Subscription_Model_Subscription $subscription) {

        $this->addFieldToFilter('status', array('in' => Adyen_Subscription_Model_Subscription::STATUS_PAYMENT_ERROR));

        $subSelect = $this->getConnection()->select();
        $subSelect->from(array('subscription_history' => $this->getTable('adyen_subscription/subscription_history')), 'MAX(date) as date');
        $subSelect->where('subscription_id = ?', $subscription->getId());
        $subSelect->where('status = ?', Adyen_Subscription_Model_Subscription::STATUS_ACTIVE);

        $this->getSelect()
            ->where("subscription_id = ?", $subscription->getId())
            ->where("date >= (?)", $subSelect)
            ->order(array('date ASC'));

        return $this;
    }
}
