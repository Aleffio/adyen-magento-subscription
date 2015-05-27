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

/**
 * Class Ho_Recurring_Model_Profile_Address
 *
 * @method int getProfileId()
 * @method Ho_Recurring_Model_Profile_Address setProfileId(int $value)
 * @method int getSource()
 * @method Ho_Recurring_Model_Profile_Address setSource(int $value)
 * @method int getType()
 * @method Ho_Recurring_Model_Profile_Address setType(int $value)
 * @method int getOrderAddressId()
 * @method Ho_Recurring_Model_Profile_Address setOrderAddressId(int $value)
 * @method int getCustomerAddressId()
 * @method Ho_Recurring_Model_Profile_Address setCustomerAddressId(int $value)
 */
class Ho_Recurring_Model_Profile_Address extends Mage_Core_Model_Abstract
{
    const ADDRESS_SOURCE_CUSTOMER = 1;
    const ADDRESS_SOURCE_ORDER    = 2;

    const ADDRESS_TYPE_BILLING  = 1;
    const ADDRESS_TYPE_SHIPPING = 2;

    protected function _construct ()
    {
        $this->_init('ho_recurring/profile_address');
    }

    /**
     * Set correct values on profile address based on given profile and order address
     *
     * @param Ho_Recurring_Model_Profile $profile
     * @param Mage_Sales_Model_Order_Address $orderAddress
     * @return $this
     */
    public function initAddress(Ho_Recurring_Model_Profile $profile, Mage_Sales_Model_Order_Address $orderAddress)
    {
        $this->setProfileId($profile->getId());

        if ($orderAddress->getAddressType() == Mage_Sales_Model_Order_Address::TYPE_BILLING) {
            $this->setType(self::ADDRESS_TYPE_BILLING);
        }
        else {
            $this->setType(self::ADDRESS_TYPE_SHIPPING);
        }

        if ($orderAddress->getCustomerAddressId()) {
            // Create customer address
            $this->setSource(self::ADDRESS_SOURCE_CUSTOMER)
                ->setCustomerAddressId($orderAddress->getCustomerAddressId());
        }
        else {
            // Create order address
            $this->setSource(self::ADDRESS_SOURCE_ORDER)
                ->setOrderAddressId($orderAddress->getId());
        }

        return $this;
    }

    /**
     * @param Ho_Recurring_Model_Profile $profile
     * @param int $type
     * @return Mage_Sales_Model_Order_Address|Mage_Customer_Model_Address
     */
    public function getAddress(Ho_Recurring_Model_Profile $profile, $type = self::ADDRESS_TYPE_BILLING)
    {
        /** @var Ho_Recurring_Model_Profile_Address $profileAddress */
        $profileAddress = $this->getCollection()
            ->addFieldToFilter('profile_id', $profile->getId())
            ->addFieldToFilter('type', $type)
            ->getFirstItem();

        if ($profileAddress->getSource() == self::ADDRESS_SOURCE_ORDER) {
            $address = Mage::getModel('sales/order_address')->load($profileAddress->getOrderAddressId());
        }
        else {
            $address = Mage::getModel('customer/address')->load($profileAddress->getCustomerAddressId());
        }

        return $address;
    }
}
