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
 * @method string getLabel()
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
 * @method int getSortOrder()
 * @method $this setSortOrder(int $value)
 */
class Ho_Recurring_Model_Product_Profile extends Mage_Core_Model_Abstract
{
    const TERM_TYPE_DAY     = 'day';
    const TERM_TYPE_WEEK    = 'week';
    const TERM_TYPE_MONTH   = 'month';
    const TERM_TYPE_QUARTER = 'quarter';
    const TERM_TYPE_YEAR    = 'year';

    protected function _construct ()
    {
        $this->_init('ho_recurring/product_profile');
    }

    /**
     * @return array
     */
    public function getTermTypes()
    {
        $helper = Mage::helper('ho_recurring');

        return array(
            self::TERM_TYPE_DAY     => $helper->__('Day'),
            self::TERM_TYPE_WEEK    => $helper->__('Week'),
            self::TERM_TYPE_MONTH   => $helper->__('Month'),
            self::TERM_TYPE_QUARTER => $helper->__('Quarter'),
            self::TERM_TYPE_YEAR    => $helper->__('Year'),
        );
    }
}
