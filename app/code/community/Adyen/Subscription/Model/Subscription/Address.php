<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

/**
 * Class Adyen_Subscription_Model_Subscription_Address
 *
 * @method int getSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Address setSubscriptionId(int $value)
 * @method int getSource()
 * @method Adyen_Subscription_Model_Subscription_Address setSource(int $value)
 * @method int getType()
 * @method Adyen_Subscription_Model_Subscription_Address setType(int $value)
 * @method int getOrderAddressId()
 * @method Adyen_Subscription_Model_Subscription_Address setOrderAddressId(int $value)
 * @method int getCustomerAddressId()
 * @method Adyen_Subscription_Model_Subscription_Address setCustomerAddressId(int $value)
 * @method int getQuoteAddressId()
 * @method Adyen_Subscription_Model_Subscription_Address setQuoteAddressId(int $value)
 */
class Adyen_Subscription_Model_Subscription_Address extends Mage_Core_Model_Abstract
{
    const ADDRESS_SOURCE_CUSTOMER = 1;
    const ADDRESS_SOURCE_ORDER    = 2;
    const ADDRESS_SOURCE_QUOTE    = 3;

    const ADDRESS_TYPE_BILLING  = 1;
    const ADDRESS_TYPE_SHIPPING = 2;

    protected function _construct()
    {
        $this->_init('adyen_subscription/subscription_address');
    }

    /**
     * Set correct values on subscription address based on given subscription and order address
     *
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @param Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function initAddress(Adyen_Subscription_Model_Subscription $subscription, $address)
    {
        $this->setSubscriptionId($subscription->getId());

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
        // Note: Only use customer address if 'save_in_addres_book' or 'same_as_billing'
        // is also checked at the address, because it's not enough to rely solely on the
        // customer address ID, because an address can be changed when creating an order
        // in the backend, but this ID still remains when a quote is converted to an order
        elseif ($address->getCustomerAddressId()
            && ($address->getData('save_in_address_book') || $address->getData('same_as_billing'))) {
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
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @param int $type
     * @return Adyen_Subscription_Model_Subscription_Address
     */
    public function getSubscriptionAddress(Adyen_Subscription_Model_Subscription $subscription, $type = self::ADDRESS_TYPE_BILLING)
    {
        $this->_getResource()->loadBySubscription($this, $subscription, $type);
        return $this;
    }

    /**
     * @param Adyen_Subscription_Model_Subscription $subscription
     * @param int $type
     * @return Mage_Sales_Model_Order_Address|Mage_Customer_Model_Address
     */
    public function getAddress(Adyen_Subscription_Model_Subscription $subscription, $type = self::ADDRESS_TYPE_BILLING)
    {
        $subscriptionAddress = $this->getSubscriptionAddress($subscription, $type);

        if ($subscriptionAddress->getSource() == self::ADDRESS_SOURCE_ORDER) {
            $address = Mage::getModel('sales/order_address')->load($subscriptionAddress->getOrderAddressId());
        }
        elseif ($subscriptionAddress->getSource() == self::ADDRESS_SOURCE_QUOTE) {
            $address = Mage::getModel('sales/quote_address')->load($subscriptionAddress->getQuoteAddressId());
        }
        else {
            $address = Mage::getModel('customer/address')->load($subscriptionAddress->getCustomerAddressId());
        }

        return $address;
    }
}
