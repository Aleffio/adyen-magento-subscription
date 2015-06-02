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
 
class Ho_Recurring_Block_Adminhtml_Sales_Order_Create
    extends Mage_Adminhtml_Block_Sales_Order_Create {

    public function __construct()
    {
        parent::__construct();
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::registry('current_profile');

        if (! $profile) {
            return $this;
        }

        $helper = Mage::helper('ho_recurring');

        $this->_removeButton('reset');
        $this->_removeButton('save');

        $confirm = Mage::helper('ho_recurring')->__('Are you sure you want to place the order now?');
        $confirm .= ' ' .Mage::helper('ho_recurring')->__('Order will be automatically created at:');
        $confirm .= ' ' .$profile->getActiveQuoteAdditional()->getScheduledAtFormatted();

        $js = <<<JS
var confirm = window.confirm('{$confirm}'); if(confirm) { order.submit() }
JS;
        $this->_updateButton('save', 'onclick', $js);

        // @todo different URL for updating profile instead of quote
        $fullUpdate = $this->getRequest()->getParam('full_update');
        $updateProfileUrl = $this->getUrl('ho_recurring/adminhtml_profile/updateProfile', ['id' => $profile->getId()]);

        $this->_addButton('save_scheduled', [
            'label' => Mage::helper('ho_recurring')->__('Finish Editing'),
            'class' => 'save',
            'onclick' => ($fullUpdate
                ? 'window.opener.document.location.href = \'' . $updateProfileUrl . '\'; window.close()'
                : "window.opener.location.reload(false); window.close()"),
        ], 20);

    }
}