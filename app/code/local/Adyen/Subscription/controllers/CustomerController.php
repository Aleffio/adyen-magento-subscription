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
    public function profilesAction()
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
        $profileId = $this->getRequest()->getParam('profile_id');

        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_forward('noRoute');
            return false;
        }

        if ($profile->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_forward('noRoute');
            return false;
        }

        Mage::register('adyen_subscription_profile', $profile);

        $this->_title($this->__('Subscription'))
            ->_title($this->__('Subscription # %s', $profile->getId()));
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('adyen_subscription/customer/profiles');
        }

        $this->renderLayout();
    }
}
