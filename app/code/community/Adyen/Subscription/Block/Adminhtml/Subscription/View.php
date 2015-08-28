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
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Subscription_View extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'adyen_subscription';
        $this->_controller = 'adminhtml_subscription';
        $this->_mode = 'view';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');

        if ($this->getSubscription()->canPause()) {
            $this->_addButton('pause_subscription', [
                'class'     => 'delete',
                'label'     => Mage::helper('adyen_subscription')->__('Pause Subscription'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/pause',
                    ['id' => $this->getSubscription()->getIncrementId()])}')",
            ], 5);
        }

        if ($this->getSubscription()->canCancel()) {
            $this->_addButton('stop_subscription', [
                'class'     => 'delete',
                'label'     => Mage::helper('adyen_subscription')->__('Stop Subscription'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/cancel',
                    ['id' => $this->getSubscription()->getIncrementId()])}')",
            ], 10);
        }

        if ($this->getSubscription()->isCanceled() || $this->getSubscription()->isPaused()) {
            $this->_addButton('activate_subscription', [
                'label'     => Mage::helper('adyen_subscription')->__('Activate Subscription'),
                'onclick' => "deleteConfirm('" . Mage::helper('adminhtml')->__('Are you sure you want to do reactivate this subscription?')
                    . "', '" . $this->getUrl('*/*/activateSubscription', ['id' => $this->getSubscription()->getId()]) . "')",
            ], 10);
        }

        if ($this->getSubscription()->canCreateQuote() && !$this->getSubscription()->getActiveQuote()) {
            $this->_addButton('create_quote', [
                'label' => Mage::helper('adyen_subscription')->__('Schedule Order'),
                'class' => 'add',
                'onclick' => "setLocation('{$this->getUrl('*/*/createQuote',
                    ['id' => $this->getSubscription()->getId()])}')",
            ], 20);
        }

        if ($this->getSubscription()->canEditSubscription()) {
            $this->_addButton('edit_subscription', [
                'label' => Mage::helper('adyen_subscription')->__('Edit Subscription'),
                'class' => 'add',
                'onclick' => "setLocation('{$this->getUrl('*/*/editSubscription',
                    ['id' => $this->getSubscription()->getId()])}')",
            ], 30);
        }
    }

    public function getHeaderText()
    {
        $subscription = $this->getSubscription();

        if ($subscription->getId()) {
            return Mage::helper('adyen_subscription')->__('Adyen Subscription %s for %s',
                sprintf('<i>#%s</i>', $subscription->getIncrementId()),
                sprintf('<i>%s</i>', $subscription->getCustomerName())
            );
        }
        else {
            return Mage::helper('adyen_subscription')->__('New Adyen Subscription');
        }
    }

    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
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
