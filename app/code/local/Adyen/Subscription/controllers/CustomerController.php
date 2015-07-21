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

class Adyen_Subscription_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();

        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Show subscriptions
     */
    public function subscriptionsAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('ho_monitor')->__('My Subscriptions'))
            ->renderLayout();
    }

    /**
     * Show subscriptions
     */
    public function viewAction()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (!$subscription->getId()) {
            $this->_forward('noRoute');
            return false;
        }

        if ($subscription->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_forward('noRoute');
            return false;
        }

        Mage::register('adyen_subscription', $subscription);

        $this->_title($this->__('Subscription'))
            ->_title($this->__('Subscription # %s', $subscription->getId()));
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('adyen_subscription/customer/subscriptions');
        }

        $this->renderLayout();
    }
}
