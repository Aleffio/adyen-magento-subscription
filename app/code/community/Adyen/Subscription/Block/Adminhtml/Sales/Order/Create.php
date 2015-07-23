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
 
class Adyen_Subscription_Block_Adminhtml_Sales_Order_Create
    extends Mage_Adminhtml_Block_Sales_Order_Create {

    public function __construct()
    {
        parent::__construct();
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::registry('current_subscription');

        if (! $subscription) {
            return $this;
        }

        $helper = Mage::helper('adyen_subscription');

        $this->_removeButton('reset');
        $this->_removeButton('save');

        $confirm = Mage::helper('adyen_subscription')->__('Are you sure you want to place the order now?');
        $confirm .= ' ' .Mage::helper('adyen_subscription')->__('Order will be automatically created at:');
        $confirm .= ' ' .$subscription->getActiveQuoteAdditional()->getScheduledAtFormatted();

        $js = <<<JS
var confirm = window.confirm('{$confirm}'); if(confirm) { order.submit() }
JS;
        $this->_updateButton('save', 'onclick', $js);

        $this->_addButton('save_scheduled', [
            'label' => Mage::helper('adyen_subscription')->__('Finish Editing'),
            'class' => 'save',
            'onclick' => "order.submitSubscription()",
        ], 20);
    }
}
