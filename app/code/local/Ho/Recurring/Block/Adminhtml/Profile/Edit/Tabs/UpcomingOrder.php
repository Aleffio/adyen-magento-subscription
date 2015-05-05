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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_UpcomingOrder extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @return Mage_Sales_Model_Resource_Quote_Item_Collection
     */
    public function getItemsCollection()
    {
        $quote = $this->getProfile()->getQuote();

        return $quote->getItemsCollection();
    }

    /**
     * @return Ho_Recurring_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('ho_recurring');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('ho_recurring')->__('Upcoming Order');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('ho_recurring')->__('Upcoming Order');
    }

    /**
     * Don't show tab if there is no quote
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getProfile()->getQuote();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
