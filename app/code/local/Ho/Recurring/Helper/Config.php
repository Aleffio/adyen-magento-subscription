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

class Ho_Recurring_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PROFILE_CANCEL_REASONS   = 'ho_recurring/profile/cancel_reasons';

    /**
     * @return array
     */
    public function getCancelReasons()
    {
        $config = Mage::getStoreConfig(self::XML_PATH_PROFILE_CANCEL_REASONS);

        return $config ? unserialize($config) : array();
    }
}
