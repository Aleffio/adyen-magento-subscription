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

        $this->loadLayout()
            ->_setActiveMenu('sales/ho_recurring_profiles')
            ->_title($helper->__('Sales'))
            ->_title($helper->__('Recurring Profiles'));

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
     * Create new profile
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * @todo page title is not showing
     */
    public function editAction()
    {
        // @todo Can't load layout, throws errors or doesn't load anything after calling _initAction
        // Maybe because of XML of ho_recurring_adminhtml_profile_edit handle?
//        $this->_initAction();

        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('ho_recurring/profile');

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ho_recurring')->__('This profile no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : Mage::helper('ho_recurring')->__('New Profile'));

        $data = Mage::getSingleton('adminhtml/session')->getProfileData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('ho_recurring', $model);

        // @see to do before commented _initAction
//        $this->_addBreadcrumb(
//            $id ? Mage::helper('ho_recurring')->__('Edit Profile') : Mage::helper('ho_recurring')->__('New Profile'),
//            $id ? Mage::helper('ho_recurring')->__('Edit Profile') : Mage::helper('ho_recurring')->__('New Profile'))
            $this->loadLayout()->renderLayout();
    }

    /**
     * Delete profile
     */
    public function deleteAction()
    {
        if ($profileId = $this->getRequest()->getParam('id')) {
            /** @var Ho_Recurring_Model_Profile $profile */
            $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

            if ($profile->getId()) {
                try {
                    $profile->delete();

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('ho_recurring')->__('The profile has been successfully deleted')
                    );
                }
                catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('ho_recurring')->__('An error occurred while trying to delete this profile')
                    );
                }
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Create profile quote
     */
    public function createQuoteAction()
    {
        if ($profileId = $this->getRequest()->getParam('id')) {
            $profile = Mage::getModel('ho_recurring/profile')->load($profileId);

            if ($profile->getId()) {
                try {
                    $quote = $profile->createQuote();

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('ho_recurring')->__('Quote successfully created (#%s)', $quote->getId())
                    );
                }
                catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('ho_recurring')->__('An error occurred while trying to create a quote for this profile: ' . $e->getMessage())
                    );
                }
            }
        }

        $this->_redirectReferer();
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
                    $order = $profile->createOrder();

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('ho_recurring')->__('Order successfully created (#%s)', $order->getIncrementId())
                    );
                }
                catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(
                        Mage::helper('ho_recurring')->__('An error occurred while trying to create a order for this profile: ' . $e->getMessage())
                    );
                }
            }
        }

        $this->_redirectReferer();
    }
}
