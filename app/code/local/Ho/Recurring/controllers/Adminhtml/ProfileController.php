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

class Ho_Recurring_Adminhtml_ProfileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize profile pages layout
     *
     * @return $this
     */
    protected function _initAction()
    {
        $helper = Mage::helper('ho_recurring');

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Recurring Profiles'));

        $this->loadLayout()
            ->_setActiveMenu('sales/ho_recurring_profiles');

        $this->_addBreadcrumb($helper->__('Sales'), $helper->__('Sales'))
            ->_addBreadcrumb($helper->__('Recurring Profiles'), $helper->__('Recurring Profiles'));

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
     * @return Ho_Recurring_Model_Profile
     */
    protected function _initProfile()
    {
        $profileId  = $this->getRequest()->getParam('id');
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        Mage::register('ho_recurring', $profile);

        return $profile;
    }

    /**
     * View Action
     */
    public function viewAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('ho_recurring');

        if (! $profile->getId()) {
            $this->_getSession()->addError($helper->__('This profile no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Recurring Profile #%s for %s',
                 $profile->getIncrementId(), $profile->getCustomerName()));

        $this->loadLayout();
        $this->_setActiveMenu('sales/ho_recurring_profiles');
        $this->renderLayout();
    }


    /**
     * Edit Action
     */
    public function editAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('ho_recurring');

        if (! $profile->getId()) {
            $this->_getSession()->addError($helper->__('This profile no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        $this->_title($helper->__('Sales'))
             ->_title($helper->__('Edit Recurring Profile #%s for %s',
                 $profile->getIncrementId(), $profile->getCustomerName()));

        $data = $this->_getSession()->getProfileData(true);
        if (!empty($data)) {
            $profile->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/ho_recurring_profiles');
        $this->renderLayout();
    }


    public function saveAction()
    {
        $profile = $this->_initProfile();
        $helper = Mage::helper('ho_recurring');

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
                Mage::helper('ho_recurring')->__('Profile successfully saved')
            );
            $this->_redirect('*/*/view', ['id' => $profile->getId()]);
        } catch (Exception $e) {
            Ho_Recurring_Exception::logException($e);

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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $reason = $this->getRequest()->getParam('reason');
        if (! $reason) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('No stop reason given')
            );
            $this->_redirect('*/*/cancel', ['id' => $profile->getId()]);
            return;
        }

        $profile->setCancelCode($reason);
        $profile->setStatus($profile::STATUS_CANCELED);
        $profile->setEndsAt(now());
        $profile->save();

        $this->_getSession()->addSuccess(
            Mage::helper('ho_recurring')->__('Profile %s successfully cancelled', $profile->getIncrementId())
        );
        $this->_redirect('*/*/');
    }

    public function activateProfileAction()
    {
        $profileId = $this->getRequest()->getParam('id');

        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $profile->activate();

            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('The profile has been successfully activated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('ho_recurring')->__('An error occurred while trying to activate this profile')
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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        if ($profile->getId()) {
            try {
                $profile->delete();

                $this->_getSession()->addSuccess(
                    Mage::helper('ho_recurring')->__('The profile has been successfully deleted')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('ho_recurring')->__('An error occurred while trying to delete this profile')
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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            $quote = Mage::getSingleton('ho_recurring/service_profile')->createQuote($profile);

            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Quote (#%s) successfully created', $quote->getId())
            );
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('ho_recurring')->__('An error occurred while trying to create a quote for this profile: ' . $e->getMessage())
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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        try {
            if (! $profile->getActiveQuote()) {
                Mage::getSingleton('ho_recurring/service_profile')->createQuote($profile);
            }

            $this->_editProfile($profile, ['full_update' => true]);
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('ho_recurring')->__('An error occurred while trying to create a quote for this profile: ' . $e->getMessage())
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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParam('ho_recurring');

        try {
            $quote = $profile->getActiveQuote();

            Mage::getModel('ho_recurring/service_quote')->updateProfile($quote, $profile);
            $profile->importPostData($postData);
            $profile->setActive()->save();

            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Profile and scheduled order successfully updated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $profile->setErrorMessage($e->getMessage());
            $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            $profile->save();

            $this->_getSession()->addError(
                Mage::helper('ho_recurring')->__('An error occurred: ' . $e->getMessage())
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
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (!$profile->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Could not find profile')
            );
            $this->_redirect('*/*/');
            return;
        }

        $postData = $this->getRequest()->getParam('ho_recurring');

        try {
            $quote = $profile->getActiveQuote();
            Mage::getModel('ho_recurring/service_quote')->updateQuotePayment($quote);

            $profile->importPostData($postData);

            $profile->save();

            $this->_getSession()->addSuccess(
                Mage::helper('ho_recurring')->__('Quote successfully updated')
            );
        }
        catch (Mage_Core_Exception $e) {
            $profile->setErrorMessage($e->getMessage());
            $profile->setStatus($profile::STATUS_PROFILE_ERROR);
            $profile->save();

            $this->_getSession()->addError(
                Mage::helper('ho_recurring')->__('An error occurred: ' . $e->getMessage())
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
            $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

            if ($profile->getId()) {
                try {
                    $quote = $profile->getActiveQuote();
                    if (! $quote) {
                        Ho_Recurring_Exception::throwException('Can\'t create order: No quote created yet.');
                    }

                    $order = Mage::getSingleton('ho_recurring/service_quote')->createOrder($quote, $profile);

                    $this->_getSession()->addSuccess(
                        Mage::helper('ho_recurring')->__('Order successfully created (#%s)', $order->getIncrementId())
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError(
                        Mage::helper('ho_recurring')->__('An error occurred while trying to create a order for this profile: ' . $e->getMessage())
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
        $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

        if (! $profile->getId()) {
            Ho_Recurring_Exception::throwException('Can\'t create order: No quote created yet.');
            $this->_redirectReferer();
        }

        $this->_editProfile($profile);
    }

    protected function _editProfile(
        Ho_Recurring_Model_Profile $profile,
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
