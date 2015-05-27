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

class Ho_Recurring_Block_Adminhtml_Profile_View_Tabs_Info
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _beforeToHtml()
    {
        Mage_Adminhtml_Block_Sales_Order_Abstract::_beforeToHtml();
    }

    /**
     * @return Ho_Recurring_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('ho_recurring');
    }

    /**
     * @return Adyen_Payment_Model_Billing_Agreement
     */
    public function getBillingAgreement()
    {
        return $this->getProfile()->getBillingAgreement();
    }

    /**
     * @return string
     */
    public function getBillingAgreementViewUrl()
    {
        return $this->getUrl('adminhtml/sales_billing_agreement/view', array('agreement' => $this->getBillingAgreement()->getId()));
    }

    /**
     * @return string
     */
    public function getCustomerViewUrl()
    {
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getProfile()->getCustomerId()));
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adminhtml')->__('Information');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adminhtml')->__('Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    public function getEditUrl($section)
    {
        return $this->getUrl('ho_recurring/adminhtml_profile/edit', [
            'id' => $this->getProfile()->getId(),
            'section' => $section
        ]);
    }

    /**
     * @param $section
     *
     * @return bool
     * @throws Exception
     */
    public function isEdit($section = null)
    {
        $editSection = $this->getRequest()->getParam('section');
        if (!$editSection) {
            return false;
        }

        if ($section === null) {
            return true;
        }

        return $section == $this->getRequest()->getParam('section');
    }
}
