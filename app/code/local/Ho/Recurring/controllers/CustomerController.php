<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_CustomerController extends Mage_Core_Controller_Front_Action
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
     * Show recurring profiles
     */
    public function profilesAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('ho_monitor')->__('My Recurring Profiles'))
            ->renderLayout();
    }

    /**
     * Show recurring profile
     */
    public function viewAction()
    {
        $profileId = $this->getRequest()->getParam('profile_id');

        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_forward('noRoute');
            return false;
        }

        if ($profile->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_forward('noRoute');
            return false;
        }

        Mage::register('ho_recurring_profile', $profile);

        $this->_title($this->__('Recurring Profile'))
            ->_title($this->__('Recurring Profile # %s', $profile->getId()));
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('ho_recurring/customer/profiles');
        }

        $this->renderLayout();
    }
}
