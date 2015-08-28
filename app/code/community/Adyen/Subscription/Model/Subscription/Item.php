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
 * Class Adyen_Subscription_Model_Subscription_Item
 *
 * @method Adyen_Subscription_Model_Subscription_Item setProductSubscriptionId(int $id)
 * @method int getProductSubscriptionId()
 * @method string getStatus()
 * @method Adyen_Subscription_Model_Subscription_Item setStatus(string $value)
 * @method int getSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Item setSubscriptionId(int $value)
 * @method int getProductId()
 * @method Adyen_Subscription_Model_Subscription_Item setProductId(int $value)
 * @method Adyen_Subscription_Model_Subscription_Item setProductOptions(string $value)
 * @method string getSku()
 * @method Adyen_Subscription_Model_Subscription_Item setSku(string $value)
 * @method string getName()
 * @method Adyen_Subscription_Model_Subscription_Item setName(string $value)
 * @method string getProductSubscriptionId()
 * @method Adyen_Subscription_Model_Subscription_Item setProductSubscriptionId(int $value)
 * @method string getLabel()
 * @method Adyen_Subscription_Model_Subscription_Item setLabel(string $value)
 * @method float getPrice()
 * @method Adyen_Subscription_Model_Subscription_Item setPrice(float $value)
 * @method float getPriceInclTax()
 * @method Adyen_Subscription_Model_Subscription_Item setPriceInclTax(float $value)
 * @method int getQty()
 * @method Adyen_Subscription_Model_Subscription_Item setQty(int $value)
 * @method bool getOnce()
 * @method Adyen_Subscription_Model_Subscription_Item setOnce(bool $value)
 * @method int getMinBillingCycles()
 * @method Adyen_Subscription_Model_Subscription_Item setMinBillingCycles(int $value)
 * @method int getMaxBillingCycles()
 * @method Adyen_Subscription_Model_Subscription_Item setMaxBillingCycles(int $value)
 * @method string getCreatedAt()
 * @method Adyen_Subscription_Model_Subscription_Item setCreatedAt(string $value)
 */
class Adyen_Subscription_Model_Subscription_Item extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE     = 'active';
    const STATUS_EXPIRED    = 'expired';

    protected function _construct ()
    {
        $this->_init('adyen_subscription/subscription_item');
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
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        if (! $this->hasData('_subscription')) {
            $subscription = Mage::getModel('adyen_subscription/subscription')->load($this->getSubscriptionId());
            $this->setData('_subscription', $subscription);
        }

        return $this->getData('_subscription');
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
