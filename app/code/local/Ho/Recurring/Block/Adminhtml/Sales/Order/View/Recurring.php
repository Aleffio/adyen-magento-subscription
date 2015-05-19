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
 * Class Ho_Recurring_Block_Adminhtml_Sales_Order_View_Recurring
 * @method $this setProfile(Ho_Recurring_Model_Profile $profile)
 * @method Ho_Recurring_Model_Profile getProfile()
 * @see ho/recurring/sales/order/view/recurring.phtml
 */
class Ho_Recurring_Block_Adminhtml_Sales_Order_View_Recurring
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * @return mixed
     */
    public function getOrderInfoData()
    {
        return $this->getParentBlock()->getOrderInfoData();
    }

    protected function _toHtml()
    {
        $order = $this->getOrder();
        $profile = Mage::getModel('ho_recurring/profile')->loadByOrder($order);

        if (! $profile->getId()) {
            return $this->getChildHtml();
        }

        $this->setProfile($profile);
        return parent::_toHtml();
    }

    /**
     * @return Ho_Recurring_Model_Profile_Order
     */
    public function getProfileOrderAdditionalInfo()
    {
        return $this->getProfile()->getOrderAdditional($this->getOrder());
    }


    /**
     * @return Ho_Recurring_Model_Profile_Quote|null
     */
    public function getProfileQuoteAdditionalInfo()
    {
        $quoteAdditional = Mage::getModel('ho_recurring/profile_quote')
            ->load($this->getOrder()->getQuoteId(), 'quote_id');

        return $quoteAdditional->getId() ? $quoteAdditional : null;
    }
}
