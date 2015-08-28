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
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, Sander Mangel <sander@sandermangel.nl>
 */

class Adyen_Subscription_Adminhtml_CommentsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Save new comment
     *
     * @return void
     */
    public function saveAction()
    {
        $postData = Mage::app()->getRequest()->getPost();
        $subscriptionsId = Mage::app()->getRequest()->getParam('subscription');

        $_subscription = Mage::getModel('adyen_subscription/subscription')->load((int)$subscriptionsId);
        if (!(int)$_subscription->getId()) {
            $response = Mage::helper('core')->jsonEncode(array(
                'error'     => true,
                'message'   => $this->__('No subscription found.')
            ));
            $this->getResponse()->setBody($response);
            return;
        }

        Mage::register('adyen_subscription', $_subscription);

        try {
            Mage::getModel('adyen_subscription/subscription_history')
                ->saveComment($_subscription, $postData['comment']);

            Mage::getSingleton('admin/session')->addSuccess('Comment saved');
        } catch(Exception $e) {
            Mage::logException($e);
            $response = Mage::helper('core')->jsonEncode(array(
                'error'     => true,
                'message'   => $this->__('Cannot add subscription comment.')
            ));
            $this->getResponse()->setBody($response);
            return;
        }

        /**
         * Reponse for ajax call
         */
        $this->loadLayout();

        $block = $this->getLayout()->createBlock(
            'adyen_subscription/adminhtml_subscription_view_comments',
            'root',
            array()
        );

        $this->getLayout()->getBlock('content')->append($block);

        $this->renderLayout();
    }
}