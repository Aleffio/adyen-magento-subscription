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
 * @category  Ho
 * @package   Ho_Recurring
 * @author    Paul Hachmang – H&O <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */



/**
 * Class Ho_Recurring_Block_Adminhtml_Profile_Edit_Info_BillingAgreement
 * @method Ho_Recurring_Block_Adminhtml_Profile_View_Tabs_Info getParentBlock()
 * @template ho/recurring/profile/edit/info/billing_agreement.phtml
 */
class Ho_Recurring_Block_Adminhtml_Profile_Edit_Info_BillingAgreement
    extends Mage_Adminhtml_Block_Template
{
    public function getProfile()
    {
        return $this->getParentBlock()->getProfile();
    }


    /**
     * @return Mage_Sales_Model_Resource_Billing_Agreement_Collection
     */
    public function getBACollection()
    {
        $collection = Mage::getResourceModel('sales/billing_agreement_collection')
            ->addFieldToFilter('customer_id', $this->getProfile()->getCustomerId())
            ->addFieldToFilter('status', Mage_Sales_Model_Billing_Agreement::STATUS_ACTIVE);
        return $collection;
    }


    /**
     * @return Mage_Sales_Model_Billing_Agreement
     */
    public function getActiveBA()
    {
        return $this->getProfile()->getBillingAgreement();
    }


    /**
     * @return bool
     */
    public function isActiveBAValid()
    {
        return (bool) $this->getBACollection()->getItemById($this->getProfile()->getBillingAgreementId());
    }
}
