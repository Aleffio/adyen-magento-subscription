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

class Adyen_Subscription_Block_Adminhtml_Profile_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'adyen_subscription';
        $this->_controller = 'adminhtml_profile';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');

        if ($this->getProfile()->canCancel()) {
            $this->_addButton('stop_profile', [
                'class'     => 'delete',
                'label'     => Mage::helper('adyen_subscription')->__('Stop Profile'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/cancel',
                    ['id' => $this->getProfile()->getIncrementId()])}')",
            ], 10);
        }

        if ($this->getProfile()->isCanceled()) {
            $this->_addButton('activate_profile', [
                'label'     => Mage::helper('adyen_subscription')->__('Activate Profile'),
                'onclick' => "deleteConfirm('" . Mage::helper('adminhtml')->__('Are you sure you want to do reactivate this profile?')
                    . "', '" . $this->getUrl('*/*/activateProfile', ['id' => $this->getProfile()->getId()]) . "')",
            ], 10);
        }

        if ($this->getProfile()->canCreateQuote()) {
            $this->_addButton('create_quote', [
                'label' => Mage::helper('adyen_subscription')->__('Schedule Order'),
                'class' => 'add',
                'onclick' => "setLocation('{$this->getUrl('*/*/createQuote',
                    ['id' => $this->getProfile()->getId()])}')",
            ], 20);
        }

        if ($this->getProfile()->canEditProfile()) {
            $this->_addButton('edit_profile', [
                'label' => Mage::helper('adyen_subscription')->__('Edit Profile'),
                'class' => 'add',
                'onclick' => "setLocation('{$this->getUrl('*/*/editProfile',
                    ['id' => $this->getProfile()->getId()])}')",
            ], 30);
        }
    }

    public function getHeaderText()
    {
        $profile = $this->getProfile();

        if ($profile->getId()) {
            return Mage::helper('adyen_subscription')->__('Recurring Profile %s for %s',
                sprintf('<i>#%s</i>', $profile->getIncrementId()),
                sprintf('<i>%s</i>', $profile->getCustomerName())
            );
        }
        else {
            return Mage::helper('adyen_subscription')->__('New Profile');
        }
    }

    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
