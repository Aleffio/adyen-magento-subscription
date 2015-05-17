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
 * Class Ho_Recurring_Model_Profile_Item
 *
 * @method string getStatus()
 * @method $this setStatus(string $value)
 * @method int getProfileId()
 * @method $this setProfileId(int $value)
 * @method int getProductId()
 * @method $this setProductId(int $value)
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
class Ho_Recurring_Model_Profile_Item extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE     = 'active';
    const STATUS_EXPIRED    = 'expired';

    protected function _construct ()
    {
        $this->_init('ho_recurring/profile_item');
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        $helper = Mage::helper('ho_recurring');

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
}
