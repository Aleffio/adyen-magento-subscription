<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

/**
 * Class Adyen_Subscription_Model_Profile_Item
 *
 * @method string getStatus()
 * @method $this setStatus(string $value)
 * @method int getProfileId()
 * @method $this setProfileId(int $value)
 * @method int getProductId()
 * @method $this setProductId(int $value)
 * @method $this setProductOptions(string $value)
 * @method string getSku()
 * @method $this setSku(string $value)
 * @method string getName()
 * @method $this setName(string $value)
 * @method string getLabel()
 * @method $this setLabel(string $value)
 * @method float getPrice()
 * @method $this setPrice(float $value)
 * @method float getPriceInclTax()
 * @method $this setPriceInclTax(float $value)
 * @method int getQty()
 * @method $this setQty(int $value)
 * @method bool getOnce()
 * @method $this setOnce(bool $value)
 * @method int getMinBillingCycles()
 * @method $this setMinBillingCycles(int $value)
 * @method int getMaxBillingCycles()
 * @method $this setMaxBillingCycles(int $value)
 * @method string getCreatedAt()
 * @method $this setCreatedAt(string $value)
 */
class Adyen_Subscription_Model_Profile_Item extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE     = 'active';
    const STATUS_EXPIRED    = 'expired';

    protected function _construct ()
    {
        $this->_init('adyen_subscription/profile_item');
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        $helper = Mage::helper('adyen_subscription');

        return array(
            self::STATUS_ACTIVE             => $helper->__('Active'),
            self::STATUS_EXPIRED            => $helper->__('Expired'),
        );
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return self::getStatuses()[$this->getStatus()];
    }

    /**
     * @return array|bool
     */
    public function getBuyRequest()
    {
        $options = $this->getProductOptions();

        return array_key_exists('info_buyRequest', $options) ? $options['info_buyRequest'] : false;
    }

    /**
     * @return array|bool
     */
    public function getAdditionalOptions()
    {
        $options = $this->getProductOptions();

        return array_key_exists('additional_options', $options) ? $options['additional_options'] : false;
    }

    /**
     * @return array
     */
    public function getProductOptions()
    {
        return unserialize($this->getData('product_options'));
    }
}
