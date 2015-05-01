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

class Ho_Recurring_Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'ho_recurring';
        $this->_controller = 'adminhtml_profile';

        parent::__construct();

        $this->_removeButton('save');
    }

    public function getHeaderText()
    {
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::registry('ho_recurring');

        if ($profile->getId()) {
            return Mage::helper('ho_recurring')->__('Recurring Profile <i>#%s</i> of <i>%s</i>',
                $profile->getId(), $profile->getCustomerName());
        }
        else {
            return Mage::helper('ho_recurring')->__('New Profile');
        }
    }
}
