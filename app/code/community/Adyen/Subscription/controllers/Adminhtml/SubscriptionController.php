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

class Adyen_Subscription_Adminhtml_SubscriptionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize subscription pages layout
     *
     * @return $this
     */
    protected function _initAction()
    {
        $helper = Mage::helper('adyen_subscription');

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Adyen Subscription'));

        $this->loadLayout()
            ->_setActiveMenu('sales/adyen_subscriptions');

        $this->_addBreadcrumb($helper->__('Sales'), $helper->__('Sales'))
            ->_addBreadcrumb($helper->__('Adyen Subscription'), $helper->__('Adyen Subscription'));

        return $this;
    }

    /**
     * Subscription grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }


    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    protected function _initSubscription()
    {
        $subscriptionId  = $this->getRequest()->getParam('id');
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        Mage::register('adyen_subscription', $subscription);

        return $subscription;
    }

    /**
     * View Action
     */
    public function viewAction()
    {
        $subscription = $this->_initSubscription();
        $helper = Mage::helper('adyen_subscription');

        if (! $subscription->getId()) {
            $this->_getSession()->addError($helper->__('This subscription no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Subscription #%s for %s',
                 $subscription->getIncrementId(), $subscription->getCustomerName()));

        $this->loadLayout();
        $this->_setActiveMenu('sales/adyen_subscriptions');
        $this->renderLayout();
    }

    /**
     * Related orders ajax action
     *
     */
    public function ordersGridAction()
    {
        $this->_initSubscription();
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Related history ajax action
     *
     */
    public function historyGridAction()
    {
        $this->_initSubscription();
        $this->loadLayout(false)
            ->renderLayout();
    }


    /**
     * Edit Action
     */
    public function editAction()
    {
        $subscription = $this->_initSubscription();
        $helper = Mage::helper('adyen_subscription');

        if (! $subscription->getId()) {
            $this->_getSession()->addError($helper->__('This subscription no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Edit Subscription #%s for %s',
                 $subscription->getIncrementId(), $subscription->getCustomerName()));

        /** @noinspection PhpUndefinedMethodInspection */
        $data = $this->_getSession()->getSubscriptionData(true);
        if (!empty($data)) {
            $subscription->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/adyen_subscriptions');
        $this->renderLayout();
    }


    public function saveAction()
    {
        $subscription = $this->_initSubscription();
        $helper = Mage::helper('adyen_subscription');

        if (! $subscription->getId()) {
            $this->_getSession()->addError($helper->__('This subscription no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getPost('subscription');

        try {
            //@todo move this logic to the model its self.
            if (isset($postData['billing_agreement_id'])) {
                $billingAgreementId = $postData['billing_agreement_id'];
                $billingAgreement = Mage::getModel('sales/billing_agreement')
                    ->load($billingAgreementId);
                $subscription->setBillingAgreement($billingAgreement, true);
            }

            $subscription->save();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setSubscriptionData(null);
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Adyen Subscription successfully saved')
            );
            $this->_redirect('*/*/view', ['id' => $subscription->getId()]);
        } catch (Exception $e) {
            Adyen_Subscription_Exception::logException($e);

            /** @noinspection PhpUndefinedMethodInspection */
            $this->_getSession()->setSubscriptionData($postData);
            $this->_getSession()->addError($helper->__(
                'There was an error saving the subscription: %s',
                $e->getMessage()
            ));
            $this->_redirectReferer();
        }
    }

    public function pauseAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');

        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (!$subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $subscription->pause();

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('The subscription has been successfully paused')
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred while trying to pause this subscription')
            );
        }

        $this->_redirectReferer();

    }

    /**
     * Subscription cancellation form
     */
    public function cancelAction()
    {
        // Get cancel reasons
        $reasons = Mage::helper('adyen_subscription/config')->getCancelReasons();
        if (!empty($reasons)) {
            $this->_initAction()->renderLayout();
        }
        else {
            // Just cancel without a reason
            $subscriptionId = $this->getRequest()->getParam('id');
            /** @var Adyen_Subscription_Model_Subscription $subscription */
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);
            $subscription->cancel(null);
            $this->_getSession()->addSuccess(Mage::helper('adyen_subscription')->__(
                'Adyen Subscription %s successfully cancelled',
                $subscription->getIncrementId()
            ));

            $this->_redirectReferer();
        }
    }

    /**
     * @throws Exception
     */
    public function cancelPostAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        $reason = $this->getRequest()->getParam('reason');
        if (! $reason) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('No stop reason given')
            );
            $this->_redirect('*/*/cancel', ['id' => $subscription->getId()]);
            return;
        }

        $subscription->cancel($reason);

        $this->_getSession()->addSuccess(Mage::helper('adyen_subscription')->__(
            'Adyen Subscription %s successfully cancelled',
            $subscription->getIncrementId()
        ));

        $this->_redirect('*/*/view', ['id' => $subscriptionId]);
    }

    public function activateSubscriptionAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');

        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (!$subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $subscription->activate();

            $this->_getSession()->addSuccess(Mage::helper('adyen_subscription')->__(
                'The subscription has been successfully activated'
            ));
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred while trying to activate this subscription')
            );
        }

        $this->_redirectReferer();
    }

    /**
     * Delete subscription
     */
    public function deleteAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($subscription->getId()) {
            try {
                $subscription->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('adyen_subscription')->__('The subscription has been successfully deleted')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('adyen_subscription')->__('An error occurred while trying to delete this subscription')
                );
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Create subscription quote
     */
    public function createQuoteAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $quote = Mage::getSingleton('adyen_subscription/service_subscription')->createQuote($subscription);

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Quote (#%s) successfully created', $quote->getId())
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('adyen_subscription')->__(
                'An error occurred while trying to create a quote for this subscription: %s',
                $e->getMessage()
            ));
        }

        $this->_redirectReferer();
    }


    /**
     * Create subscription quote
     */
    public function editSubscriptionAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            if (! $subscription->getActiveQuote()) {
                Mage::getSingleton('adyen_subscription/service_subscription')->createQuote($subscription);
            }

            $this->_editSubscription($subscription, ['full_update' => true]);
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('adyen_subscription')->__(
                'An error occurred while trying to create a quote for this subscription: %s',
                $e->getMessage()
            ));
        }

        $this->_redirectReferer();
    }

    /**
     * Update subscription based on edited quote
     */
    public function updateSubscriptionAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (!$subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParams();

        try {
            $quote = $subscription->getActiveQuote();

            $subscription->setActive();
            $subscription->importPostData($postData);
            Mage::getModel('adyen_subscription/service_quote')->updateSubscription($quote, $subscription);

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Adyen Subscription and scheduled order successfully updated')
            );
        }
        catch (Exception $e) {

            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred: ' . $e->getMessage())
            );
        }

        $this->_redirect('*/*/view', ['id' => $subscription->getId()]);
    }

    /**
     * Quote is automatically updated, we only need to save the custom values at the subscription (i.e. scheduled_at)
     */
    public function updateQuoteAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (!$subscription->getId()) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('Could not find subscription')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParams();

        try {
            $quote = $subscription->getActiveQuote();
            $billingAgreement = Mage::getModel('adyen_subscription/service_quote')->getBillingAgreement($quote);
            $subscription->importPostData($postData);

            Mage::getModel('adyen_subscription/service_quote')->updateQuotePayment($quote, $billingAgreement, $subscription->getData('payment'));

            $quote->getBillingAddress()->setCustomerAddressId(null)->save();
            $quote->getShippingAddress()->setCustomerAddressId(null)->save();
            if ($subscription->getData('billing_customer_address_id')
                && $subscription->getData('billing_address_save_in_address_book')) {
                $quote->getBillingAddress()
                    ->setCustomerAddressId($subscription->getData('billing_customer_address_id'))->save();

                if ($subscription->getData('shipping_as_billing')) {
                    $quote->getShippingAddress()
                        ->setCustomerAddressId($subscription->getData('billing_customer_address_id'))->save();
                }
            }
            if ($subscription->getData('shipping_customer_address_id')
                && $subscription->getData('shipping_address_save_in_address_book')) {
                $quote->getShippingAddress()
                    ->setCustomerAddressId($subscription->getData('shipping_customer_address_id'))->save();
            }

            $subscription->save();

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Quote successfully updated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $subscription->setErrorMessage($e->getMessage());
            $subscription->setStatus($subscription::STATUS_SUBSCRIPTION_ERROR);
            $subscription->save();

            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred: ' . $e->getMessage())
            );
        }

        $this->_redirect('*/*/view', ['id' => $subscription->getId()]);
    }

    /**
     * Create subscription order
     */
    public function createOrderAction()
    {
        if ($subscriptionId = $this->getRequest()->getParam('id')) {
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

            if ($subscription->getId()) {
                try {
                    $quote = $subscription->getActiveQuote();
                    if (! $quote) {
                        Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
                    }

                    $order = Mage::getSingleton('adyen_subscription/service_quote')->createOrder($quote, $subscription);

                    $this->_getSession()->addSuccess(Mage::helper('adyen_subscription')->__(
                        'Order successfully created (#%s)',
                        $order->getIncrementId()
                    ));
                } catch (Exception $e) {
                    $this->_getSession()->addError(Mage::helper('adyen_subscription')->__(
                        'An error occurred while trying to create a order for this subscription: %s',
                        $e->getMessage()
                    ));
                }
            }
        }

        $this->_redirectReferer();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
    
    public function editQuoteAction()
    {
        $subscriptionId = $this->getRequest()->getParam('id');
        $subscription = Mage::getModel('adyen_subscription/subscription')->load($subscriptionId);

        if (! $subscription->getId()) {
            Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
            $this->_redirectReferer();
        }

        $this->_editSubscription($subscription);
    }

    protected function _editSubscription(
        Adyen_Subscription_Model_Subscription $subscription,
        array $params = array())
    {
        $quote = $subscription->getActiveQuote();

        Mage::getSingleton('adminhtml/session_quote')
            ->setCustomerId($quote->getCustomerId())
            ->setStoreId($quote->getStoreId())
            ->setQuoteId($quote->getId());

        $params['subscription'] = $subscription->getId();

        $this->_redirect('adminhtml/sales_order_create/index', $params);
    }

    public function massDeleteAction()
    {
        $subscriptionIds = $this->getRequest()->getParam('subscription_id');
        // $this->getMassactionBlock()->setFormFieldName('subscription_id');
        // from Adyen_Subscription_Block_Adminhtml_Subscription_Grid

        if(!is_array($subscriptionIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('adyen_subscription')->__('Please select subscription(s).')
            );
        } else {
            try {
                $subscriptionModel = Mage::getModel('adyen_subscription/subscription');
                foreach ($subscriptionIds as $subscriptionId) {
                    $subscriptionModel->load($subscriptionId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adyen_subscription')->__(
                        'Total of %d record(s) were deleted.', count($subscriptionIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/adyen_subscriptions');
    }


    /**
     * Cutomer adyen subscription ajax action
     *
     */
    public function customerGridAction()
    {
        $this->_initCustomer();
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Initialize customer by ID specified in request
     *
     * @return Mage_Adminhtml_Sales_Billing_AgreementController
     */
    protected function _initCustomer()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');

        if ($customerId) {
            $customer->load($customerId);
        }
        Mage::register('current_customer', $customer);
        return $this;
    }



}
