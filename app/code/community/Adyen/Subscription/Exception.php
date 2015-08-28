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
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Exception extends Mage_Core_Exception
{
    /**
     * Throw an Adyen_Subscription_Exception and log it.
     * @param      $message
     * @param null $messageStorage
     *
     * @throws Adyen_Subscription_Exception
     */
    public static function throwException($message, $messageStorage = null)
    {
        if ($messageStorage && ($storage = Mage::getSingleton($messageStorage))) {
            $storage->addError($message);
        }
        $exception = new Adyen_Subscription_Exception($message);
        self::logException($exception);

        throw $exception;
    }


    /**
     * Log an Adyen_Subscription_Exception
     * @param Exception $e
     */
    public static function logException(Exception $e)
    {
        Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'adyen_subscription_exception.log');
    }
}
