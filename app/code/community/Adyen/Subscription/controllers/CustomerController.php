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
            ->_title(Mage::helper('adyen_subscription')->__('My Subscriptions'))
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

    /**
     * Pause subscription
     */
    public function pauseAction()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        if($subscriptionId) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

            if(Mage::getStoreConfigFlag('adyen_subscription/subscription/allow_pause_resume_subscription', Mage::app()->getStore())) {
                // pause subscription
                $subscription->pause();

                Mage::getSingleton('customer/session')->addSuccess(
                    Mage::helper('adyen_subscription')->__('Subscription %s successfully paused', $subscription->getIncrementId())
                );

            } else {
                Mage::getSingleton('customer/session')->addError(
                    Mage::helper('adyen_subscription')->__('Something went wrong')
                );
            }

            $this->_redirect('adyen_subscription/customer/view/subscription_id/'.$subscriptionId);
            return;
        }
        $this->_redirect('adyen_subscription/customer/subscriptions');
    }

    /**
     * Resume subscription
     */
    public function resumeAction()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        if($subscriptionId) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

            if(Mage::getStoreConfigFlag('adyen_subscription/subscription/allow_pause_resume_subscription', Mage::app()->getStore())) {
                // resume subscription
                $subscription->activate();

                Mage::getSingleton('customer/session')->addSuccess(
                    Mage::helper('adyen_subscription')->__('Subscription %s successfully activated', $subscription->getIncrementId())
                );

            } else {
                Mage::getSingleton('customer/session')->addError(
                    Mage::helper('adyen_subscription')->__('Something went wrong')
                );
            }

            $this->_redirect('adyen_subscription/customer/view/subscription_id/'.$subscriptionId);
            return;
        }
        $this->_redirect('adyen_subscription/customer/subscriptions');
    }


    /**
     * Subscription cancellation form
     */
    public function cancelAction()
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

    /**
     * @throws Exception
     */
    public function cancelPostAction()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');

        if($subscriptionId) {
            /** @var Adyen_Subscription_Model_Subscription $subscription */
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

            if( Mage::getStoreConfigFlag('adyen_subscription/subscription/allow_cancel_subscription', Mage::app()->getStore())
                && $this->getRequest()->getParam('reason')) {
                // cancel subscription with this reason
                $reason = $this->getRequest()->getParam('reason');
                $subscription->cancel($reason);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('adyen_subscription')->__(
                    'Subscription %s successfully cancelled', $subscription->getIncrementId()
                ));

            } else {
                Mage::getSingleton('customer/session')->addError(
                    Mage::helper('adyen_subscription')->__('Something went wrong')
                );
            }

            $this->_redirect('adyen_subscription/customer/view/subscription_id/'.$subscriptionId);
            return;
        }
        $this->_redirect('adyen_subscription/customer/subscriptions');
    }

}
