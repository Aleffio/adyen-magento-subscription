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

class Adyen_Subscription_Block_Adminhtml_Subscription_Cancel extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'adyen_subscription';
        $this->_controller = 'adminhtml_subscription';
        $this->_mode = 'cancel';
        $this->_headerText = Mage::helper('adyen_subscription')->__('Cancel Subscription');

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_updateButton('save', 'label', $this->__('Confirm Cancellation'));
    }
}
