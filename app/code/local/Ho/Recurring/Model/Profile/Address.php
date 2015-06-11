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
 * @method int getQuoteAddressId()
 * @method Ho_Recurring_Model_Profile_Address setQuoteAddressId(int $value)
 */
class Ho_Recurring_Model_Profile_Address extends Mage_Core_Model_Abstract
{
    const ADDRESS_SOURCE_CUSTOMER = 1;
    const ADDRESS_SOURCE_ORDER    = 2;
    const ADDRESS_SOURCE_QUOTE    = 3;

    const ADDRESS_TYPE_BILLING  = 1;
    const ADDRESS_TYPE_SHIPPING = 2;

    protected function _construct()
    {
        $this->_init('ho_recurring/profile_address');
    }

    /**
     * Set correct values on profile address based on given profile and order address
     *
     * @param Ho_Recurring_Model_Profile $profile
     * @param Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function initAddress(Ho_Recurring_Model_Profile $profile, $address)
    {
        $this->setProfileId($profile->getId());

        // Reset (possible) original values
        $this->setOrderAddressId(null)
            ->setCustomerAddressId(null)
            ->setQuoteAddressId(null);

        if ($address->getAddressType() == Mage_Sales_Model_Order_Address::TYPE_BILLING) {
            $this->setType(self::ADDRESS_TYPE_BILLING);
        }
        else {
            $this->setType(self::ADDRESS_TYPE_SHIPPING);
        }

        if ($address instanceof Mage_Sales_Model_Quote_Address) {
            // Create quote address
            $this->setSource(self::ADDRESS_SOURCE_QUOTE)
                ->setQuoteAddressId($address->getId());
        }
        elseif ($address->getCustomerAddressId()) {
            // Create customer address
            $this->setSource(self::ADDRESS_SOURCE_CUSTOMER)
                ->setCustomerAddressId($address->getCustomerAddressId());
        }
        else {
            // Create order address
            $this->setSource(self::ADDRESS_SOURCE_ORDER)
                ->setOrderAddressId($address->getId());
        }

        return $this;
    }

    /**
     * @param Ho_Recurring_Model_Profile $profile
     * @param int $type
     * @return Ho_Recurring_Model_Profile_Address
     */
    public function getProfileAddress(Ho_Recurring_Model_Profile $profile, $type = self::ADDRESS_TYPE_BILLING)
    {
        $this->_getResource()->loadByProfile($this, $profile, $type);
        return $this;
    }

    /**
     * @param Ho_Recurring_Model_Profile $profile
     * @param int $type
     * @return Mage_Sales_Model_Order_Address|Mage_Customer_Model_Address
     */
    public function getAddress(Ho_Recurring_Model_Profile $profile, $type = self::ADDRESS_TYPE_BILLING)
    {
        $profileAddress = $this->getProfileAddress($profile, $type);

        if ($profileAddress->getSource() == self::ADDRESS_SOURCE_ORDER) {
            $address = Mage::getModel('sales/order_address')->load($profileAddress->getOrderAddressId());
        }
        elseif ($profileAddress->getSource() == self::ADDRESS_SOURCE_QUOTE) {
            $address = Mage::getModel('sales/quote_address')->load($profileAddress->getQuoteAddressId());
        }
        else {
            $address = Mage::getModel('customer/address')->load($profileAddress->getCustomerAddressId());
        }

        return $address;
    }
}
