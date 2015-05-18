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
 
class Ho_Recurring_Block_Adminhtml_Sales_Order_Create_Header
    extends Mage_Adminhtml_Block_Sales_Order_Create_Header {

    /**
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::registry('current_profile');

        if (! $profile) {
            return parent::_toHtml();
        }


        $out = Mage::helper('ho_recurring')->__(
            'Edit Upcoming Orer for Profile #%s for %s in %s',
            $profile->getId(),
            $profile->getCustomer()->getName(),
            $this->getStore()->getName()
        );

        $out = $this->escapeHtml($out);
        $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
        return $out;
    }
}
