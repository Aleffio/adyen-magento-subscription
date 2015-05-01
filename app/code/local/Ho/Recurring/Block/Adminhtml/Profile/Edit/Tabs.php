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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ho_recurring')->__('Manage Profile'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => Mage::helper('ho_recurring')->__('General'),
            'title' => Mage::helper('ho_recurring')->__('General'),
            'content' => $this->getLayout()->createBlock('ho_recurring/adminhtml_profile_edit_tabs_general')->toHtml(),
        ));

        $this->addTab('products', array(
            'label' => Mage::helper('ho_recurring')->__('Recurring Products'),
            'title' => Mage::helper('ho_recurring')->__('Recurring Products'),
            'content' => $this->getLayout()->createBlock('ho_recurring/adminhtml_profile_edit_tabs_products')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
