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

class Ho_Recurring_Model_Resource_Profile_Address extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ho_recurring/profile_address', 'item_id');
    }

    /**
     * @param Ho_Recurring_Model_Profile_Address $object
     * @param Ho_Recurring_Model_Profile $profile
     * @param int $type
     * @return $this
     */
    public function loadByProfile(
        Ho_Recurring_Model_Profile_Address $object,
        Ho_Recurring_Model_Profile $profile,
        $type
    ) {
        $select = Mage::getResourceModel('ho_recurring/profile_address_collection')
            ->addFieldToFilter('profile_id', $profile->getId())
            ->addFieldToFilter('type', $type)
            ->getSelect();

        $select->reset($select::COLUMNS);
        $select->columns('item_id');

        $addressId = $this->_getConnection('read')->fetchOne($select);

        $this->load($object, $addressId);

        return $this;
    }
}
