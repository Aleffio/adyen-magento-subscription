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

class Adyen_Subscription_Adminhtml_ProfileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize profile pages layout
     *
     * @return $this
     */
    protected function _initAction()
    {
        $helper = Mage::helper('adyen_subscription');

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Subscription'));

        $this->loadLayout()
            ->_setActiveMenu('sales/adyen_subscription_profiles');

        $this->_addBreadcrumb($helper->__('Sales'), $helper->__('Sales'))
            ->_addBreadcrumb($helper->__('Subscription'), $helper->__('Subscription'));

        return $this;
    }

    /**
     * Profile grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }


    /**
     * @return Adyen_Subscription_Model_Profile
     */
    protected function _initProfile()
    {
        $profileId  = $this->getRequest()->getParam('id');
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        Mage::register('adyen_subscription', $profile);

        return $profile;
    }

    /**
     * View Action
     */
    public function viewAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('adyen_subscription');

        if (! $profile->getId()) {
            $this->_getSession()->addError($helper->__('This profile no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Subscription #%s for %s',
                 $profile->getIncrementId(), $profile->getCustomerName()));

        $this->loadLayout();
        $this->_setActiveMenu('sales/adyen_subscription_profiles');
        $this->renderLayout();
    }


    /**
     * Edit Action
     */
    public function editAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('adyen_subscription');

        if (! $profile->getId()) {
            $this->_getSession()->addError($helper->__('This profile no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Edit Subscription #%s for %s',
                 $profile->getIncrementId(), $profile->getCustomerName()));

        $data = $this->_getSession()->getProfileData(true);
        if (!empty($data)) {
            $profile->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/adyen_subscription_profiles');
        $this->renderLayout();
    }


    public function saveAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('adyen_subscription');

        if (! $profile->getId()) {
            $this->_getSession()->addError($helper->__('This profile no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getPost('profile');

        try {
            //@todo move this logic to the model its self.
            if (isset($postData['billing_agreement_id'])) {
                $billingAgreementId = $postData['billing_agreement_id'];
                $billingAgreement = Mage::getModel('sales/billing_agreement')
                    ->load($billingAgreementId);
                $profile->setBillingAgreement($billingAgreement, true);
            }

            $profile->save();

            $this->_getSession()->setProfileData(null);
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Profile successfully saved')
            );
            $this->_redirect('*/*/view', ['id' => $profile->getId()]);
        } catch (Exception $e) {
            Adyen_Subscription_Exception::logException($e);

            $this->_getSession()->setProfileData($postData);
            $this->_getSession()->addError($helper->__('There was an error saving the profile: %s', $e->getMessage()));
            $this->_redirectReferer();
        }
    }

    /**
     * Profile cancellation form
     */
    public function cancelAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * @throws Exception
     */
    public function cancelPostAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $reason = $this->getRequest()->getParam('reason');
        if (! $reason) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('No stop reason given')
            );
            $this->_redirect('*/*/cancel', ['id' => $profile->getId()]);
            return;
        }

        $profile->setCancelCode($reason);
        $profile->setStatus($profile::STATUS_CANCELED);
        $profile->setEndsAt(now());
        $profile->save();

        $this->_getSession()->addSuccess(
            Mage::helper('adyen_subscription')->__('Profile %s successfully cancelled', $profile->getIncrementId())
        );
        $this->_redirect('*/*/');
    }

    public function activateProfileAction()
    {
        $profileId = $this->getRequest()->getParam('id');

        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $profile->activate();

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('The profile has been successfully activated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred while trying to activate this profile')
            );
        }

        $this->_redirectReferer();
    }

    /**
     * Delete profile
     */
    public function deleteAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($profile->getId()) {
            try {
                $profile->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('adyen_subscription')->__('The profile has been successfully deleted')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('adyen_subscription')->__('An error occurred while trying to delete this profile')
                );
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Create profile quote
     */
    public function createQuoteAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $quote = Mage::getSingleton('adyen_subscription/service_profile')->createQuote($profile);

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Quote (#%s) successfully created', $quote->getId())
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred while trying to create a quote for this profile: ' . $e->getMessage())
            );
        }

        $this->_redirectReferer();
    }


    /**
     * Create profile quote
     */
    public function editProfileAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            if (! $profile->getActiveQuote()) {
                Mage::getSingleton('adyen_subscription/service_profile')->createQuote($profile);
            }

            $this->_editProfile($profile, ['full_update' => true]);
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred while trying to create a quote for this profile: ' . $e->getMessage())
            );
        }

        $this->_redirectReferer();
    }

    /**
     * Update profile based on edited quote
     */
    public function updateProfileAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParam('adyen_subscription');

        try {
            $quote = $profile->getActiveQuote();

            Mage::getModel('adyen_subscription/service_quote')->updateProfile($quote, $profile);
            $profile->importPostData($postData);
            $profile->setActive()->save();

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Profile and scheduled order successfully updated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $profile->setErrorMessage($e->getMessage());
            $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            $profile->save();

            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred: ' . $e->getMessage())
            );
        }

        $this->_redirect('*/*/view', ['id' => $profile->getId()]);
    }

    /**
     * Quote is automatically updated, we only need to save the custom values at the profile (i.e. scheduled_at)
     */
    public function updateQuoteAction()
    {
        $profileId = $this->getRequest()->getParam('id');
        /** @var Adyen_Subscription_Model_Profile $profile */
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParam('adyen_subscription');

        try {
            $quote = $profile->getActiveQuote();
            Mage::getModel('adyen_subscription/service_quote')->updateQuotePayment($quote);

            $profile->importPostData($postData);

            $profile->save();

            $this->_getSession()->addSuccess(
                Mage::helper('adyen_subscription')->__('Quote successfully updated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $profile->setErrorMessage($e->getMessage());
            $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            $profile->save();

            $this->_getSession()->addError(
                Mage::helper('adyen_subscription')->__('An error occurred: ' . $e->getMessage())
            );
        }

        $this->_redirect('*/*/view', ['id' => $profile->getId()]);
    }

    /**
     * Create profile order
     */
    public function createOrderAction()
    {
        if ($profileId = $this->getRequest()->getParam('id')) {
            $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

            if ($profile->getId()) {
                try {
                    $quote = $profile->getActiveQuote();
                    if (! $quote) {
                        Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
                    }

                    $order = Mage::getSingleton('adyen_subscription/service_quote')->createOrder($quote, $profile);

                    $this->_getSession()->addSuccess(
                        Mage::helper('adyen_subscription')->__('Order successfully created (#%s)', $order->getIncrementId())
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        Mage::helper('adyen_subscription')->__('An error occurred while trying to create a order for this profile: ' . $e->getMessage())
                    );
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
        $profileId = $this->getRequest()->getParam('id');
        $profile = Mage::getModel('adyen_subscription/profile')->load($profileId);

        if (! $profile->getId()) {
            Adyen_Subscription_Exception::throwException('Can\'t create order: No quote created yet.');
            $this->_redirectReferer();
        }

        $this->_editProfile($profile);
    }

    protected function _editProfile(
        Adyen_Subscription_Model_Profile $profile,
        array $params = [])
    {
        $quote = $profile->getActiveQuote();

        Mage::getSingleton('adminhtml/session_quote')
            ->setCustomerId($quote->getCustomerId())
            ->setStoreId($quote->getStoreId())
            ->setQuoteId($quote->getId());

        $params['profile'] = $profile->getId();

        $this->_redirect('adminhtml/sales_order_create/index', $params);
    }
}
