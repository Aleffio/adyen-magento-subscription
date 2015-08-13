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
 * Class Adyen_Subscription_Model_Product_Subscription
 *
 * @method int getProductId()
 * @method $this setProductId(int $value)
 * @method $this setLabel(string $value)
 * @method int getWebsiteId()
 * @method $this setWebsiteId(int $value)
 * @method int getCustomerGroupId()
 * @method $this setCustomerGroupId(int $value)
 * @method int getTerm()
 * @method $this setTerm(int $value)
 * @method string getTermType()
 * @method $this setTermType(string $value)
 * @method int getMinBillingCycles()
 * @method $this setMinBillingCycles(int $value)
 * @method int getMaxBillingCycles()
 * @method $this setMaxBillingCycles(int $value)
 * @method int getQty()
 * @method $this setQty(int $value)
 * @method float getPrice()
 * @method $this setPrice(float $value)
 * @method int getUpdatePrice()
 * @method $this setUpdatePrice(int $update)
 * @method bool getShowOnFrontend()
 * @method $this setShowOnFrontend(bool $value)
 * @method int getSortOrder()
 * @method $this setSortOrder(int $value)
 */
class Adyen_Subscription_Model_Product_Subscription extends Mage_Core_Model_Abstract
{
    const TERM_TYPE_DAY   = 'day';
    const TERM_TYPE_WEEK  = 'week';
    const TERM_TYPE_MONTH = 'month';
    const TERM_TYPE_YEAR  = 'year';

    const TYPE_DISABLED                  = 0;
    const TYPE_ENABLED_ALLOW_STANDALONE  = 1;
    const TYPE_ENABLED_ONLY_SUBSCRIPTION = 2;

    protected $_eventPrefix = 'adyen_product_subscription';

    protected function _construct ()
    {
        $this->_init('adyen_subscription/product_subscription');
    }

    /**
     * @param int|Mage_Core_Model_Store|null $store
     * @return string
     */
    public function getLabel($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }
        if ($storeLabel = $this->getStoreLabel($store)) {
            return $storeLabel;
        }

        return $this->getData('label');
    }

    /**
     * @param int|Mage_Core_Model_Store $store
     * @return bool|string
     */
    public function getStoreLabel($store)
    {
        if (!$store) {
            return false;
        }

        $label = Mage::getModel('adyen_subscription/product_subscription_label')->loadBySubscription($this, $store);

        if ($label->getId()) {
            return $label->getLabel();
        }

        return false;
    }


    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (! $this->hasData('_product')) {
            $this->setData('_product', Mage::getModel('catalog/product')->load($this->getProductId()));
        }

        return $this->getData('_product');
    }

    /**
     * @param bool $multiple
     * @return array
     */
    public function getTermTypes($multiple = false)
    {
        $helper = Mage::helper('adyen_subscription');

        return array(
            self::TERM_TYPE_DAY   => $multiple ? $helper->__('Days')   : $helper->__('Day'),
            self::TERM_TYPE_WEEK  => $multiple ? $helper->__('Weeks')  : $helper->__('Week'),
            self::TERM_TYPE_MONTH => $multiple ? $helper->__('Months') : $helper->__('Month'),
            self::TERM_TYPE_YEAR  => $multiple ? $helper->__('Years')  : $helper->__('Year'),
        );
    }

    /**
     * @return string
     */
    public function getFrontendLabel()
    {
        $multiple = $this->getTerm() > 1;
        $termType = $this->getTermTypes($multiple)[$this->getTermType()];
        if ($multiple) {
            return Mage::helper('adyen_subscription')->__("%s (Every %s %s)", $this->getLabel(), $this->getTerm(), $termType);
        }
        return Mage::helper('adyen_subscription')->__("%s (Every %s)", $this->getLabel(), $termType);
    }

    /**
     * @return string
     */
    public function getAdminLabel()
    {
        $helper = Mage::helper('core');

        $label = $this->getFrontendLabel();

        $termType = $this->getTermTypes()[$this->getTermType()];

        $pricePerTerm = $this->getPrice() / $this->getTerm();

        $priceLabel = Mage::helper('adyen_subscription')->__("(%s - %s per %s)",
            $helper->formatPrice($this->getPrice(), false),
            $helper->formatPrice($pricePerTerm, false),
            $termType
        );

        return $label . ' ' . $priceLabel;
    }


    /**
     * When we need to update the prices of all the current subscriptions we schedule a job
     * @return Mage_Core_Model_Abstract
     * @throws Exception
     */
    protected function _afterSave()
    {
        if (! $this->getData('update_price')) {
            return parent::_afterSave();
        }

        $jobCollection = Mage::getModel('cron/schedule')->getCollection()
            ->addFieldToFilter('job_code', 'adyen_subscription_create_update_prices')
            ->addFieldToFilter('status', Mage_Cron_Model_Schedule::STATUS_PENDING);

        //we already have a pending job, no need to plan in a second job.
        if ($jobCollection->count() > 0) {
            return parent::_afterSave();
        }

        //schedule a new job
        $schedule = Mage::getModel('cron/schedule');
        $schedule->setJobCode('adyen_subscription_create_update_prices');
        $schedule->schedule();
        $schedule->save();

        return parent::_afterSave();
    }
}
