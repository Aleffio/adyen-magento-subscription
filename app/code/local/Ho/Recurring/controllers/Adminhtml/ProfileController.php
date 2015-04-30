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
}
