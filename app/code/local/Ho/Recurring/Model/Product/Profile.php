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
 * Class Ho_Recurring_Model_Product_Profile
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
 * @method bool getShowOnFrontend()
 * @method $this setShowOnFrontend(bool $value)
 * @method int getSortOrder()
 * @method $this setSortOrder(int $value)
 */
class Ho_Recurring_Model_Product_Profile extends Mage_Core_Model_Abstract
{
    const TERM_TYPE_DAY     = 'day';
    const TERM_TYPE_WEEK    = 'week';
    const TERM_TYPE_MONTH   = 'month';
    const TERM_TYPE_YEAR    = 'year';

    const TYPE_DISABLED                 = 0;
    const TYPE_ENABLED_ALLOW_STANDALONE = 1;
    const TYPE_ENABLED_ONLY_PROFILE     = 2;

    protected function _construct ()
    {
        $this->_init('ho_recurring/product_profile');
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

        $label = Mage::getModel('ho_recurring/product_profile_label')->loadByProfile($this, $store);

        if ($label->getId()) {
            return $label->getLabel();
        }

        return false;
    }

    /**
     * @param bool $multiple
     * @return array
     */
    public function getTermTypes($multiple = false)
    {
        $helper = Mage::helper('ho_recurring');

        return array(
            self::TERM_TYPE_DAY     => $multiple ? $helper->__('Days') : $helper->__('Day'),
            self::TERM_TYPE_WEEK    => $multiple ? $helper->__('Weeks') : $helper->__('Week'),
            self::TERM_TYPE_MONTH   => $multiple ? $helper->__('Months') : $helper->__('Month'),
            self::TERM_TYPE_YEAR    => $multiple ? $helper->__('Years') : $helper->__('Year'),
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
            return sprintf("%s (Every %s %s)", $this->getLabel(), $this->getTerm(), $termType);
        }
        return sprintf("%s (Every %s)", $this->getLabel(), $termType);
    }
}
