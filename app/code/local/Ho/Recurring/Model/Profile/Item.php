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
 * @method int getProfileId()
 * @method setProfileId(int $value)
 * @method int getProductId()
 * @method setProductId(int $value)
 * @method string getSku()
 * @method setSku(string $value)
 * @method string getName()
 * @method setName(string $value)
 * @method float getPrice()
 * @method setPrice(float $value)
 * @method int getQty()
 * @method setQty(int $value)
 * @method bool getOnce()
 * @method setOnce(bool $value)
 * @method string getCreatedAt()
 * @method setCreatedAt(string $value)
 * @method int getStatus()
 * @method setStatus(int $value)
 */
class Ho_Recurring_Model_Profile_Item extends Mage_Core_Model_Abstract
{
    const STATUS_ACTIVE     = 1;
    const STATUS_INACTIVE   = 0;

    protected function _construct ()
    {
        $this->_init('ho_recurring/profile_item');
    }
}
