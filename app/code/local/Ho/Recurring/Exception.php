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

class Ho_Recurring_Exception extends Mage_Core_Exception
{
    /**
     * Throw an Ho_Recurring_Exception and log it.
     * @param      $message
     * @param null $messageStorage
     *
     * @throws Ho_Recurring_Exception
     */
    public static function throwException($message, $messageStorage = null)
    {
        if ($messageStorage && ($storage = Mage::getSingleton($messageStorage))) {
            $storage->addError($message);
        }
        $exception = new Ho_Recurring_Exception($message);
        self::logException($exception);

        throw $exception;
    }


    /**
     * Log an Ho_Recurring_Exception
     * @param Exception $e
     */
    public static function logException(Exception $e)
    {
        Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'ho_recurring_exception.log');
    }
}
