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

class Adyen_Subscription_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_PATH_GENERAL_SHOW_TERM_LABEL      = 'adyen_subscription/general/show_term_label';
    const XML_PATH_GENERAL_PRICE_INCLUDES_TAX   = 'adyen_subscription/general/price_includes_tax';

    const XML_PATH_SUBSCRIPTION_CANCEL_REASONS  = 'adyen_subscription/subscription/cancel_reasons';
    const XML_PATH_SUBSCRIPTION_CANCEL_ORDERS   = 'adyen_subscription/subscription/cancel_delete_orders';
    const XML_PATH_SUBSCRIPTION_HOLD_ORDERS     = 'adyen_subscription/subscription/pause_hold_orders';

    const XML_PATH_ORDER_REORDER_SUBSCRIPTION   = 'adyen_subscription/order/reorder_subscription';
    const XML_PATH_ORDER_PROTECTED_STATUSES     = 'adyen_subscription/order/protected_statuses';

    const XML_PATH_ADVANCED_SCHEDULE_QUOTES_TERM= 'adyen_subscription/advanced/schedule_quotes_term';

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return bool
     */
    public function getShowTermLabel($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_SHOW_TERM_LABEL, $store);
    }

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return mixed
     */
    public function getPriceIncludesTax($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_PRICE_INCLUDES_TAX, $store);
    }

    /**
     * @return array
     */
    public function getCancelReasons()
    {
        $config = Mage::getStoreConfig(self::XML_PATH_SUBSCRIPTION_CANCEL_REASONS);

        return $config ? unserialize($config) : array();
    }

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return bool
     */
    public function getCancelOrders($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SUBSCRIPTION_CANCEL_ORDERS, $store);
    }

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return bool
     */
    public function getHoldOrders($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SUBSCRIPTION_HOLD_ORDERS, $store);
    }

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return bool
     */
    public function getReorderSubscription($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ORDER_REORDER_SUBSCRIPTION, $store);
    }

    /**
     * @param null|Mage_Core_Model_Store|int $store
     * @return array
     */
    public function getProtectedStatuses($store = null)
    {
        return explode(',', Mage::getStoreConfig(self::XML_PATH_ORDER_PROTECTED_STATUSES, $store));
    }

    /**
     * @return string
     */
    public function getScheduleQuotesTerm()
    {
        return Mage::getStoreConfig(self::XML_PATH_ADVANCED_SCHEDULE_QUOTES_TERM);
    }

    /**
     * @return array
     */
    public function getSubscriptionPaymentMethods()
    {
        $_types = Mage::getConfig()->getNode('default/adyen_subscription/allowed_payment_methods');
        if (! $_types) {
            return array();
        }

        $types = array();
        foreach ($_types->asArray() as $data) {
            $types[$data['code']] = $data['label'];
        }
        return $types;
    }

    public function getSelectedSubscriptionPaymentMethods($storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }

        $subscriptionPaymentMethods = $this->getSubscriptionPaymentMethods();
        $selectedSubscriptionPaymentMethods = Mage::getStoreConfig("adyen_subscription/subscription/allowed_payment_methods", $storeId);

        if ($selectedSubscriptionPaymentMethods) {
            $selectedSubscriptionPaymentMethods = explode(',', $selectedSubscriptionPaymentMethods);
            foreach ($subscriptionPaymentMethods as $code => $label) {
                if (!in_array($code, $selectedSubscriptionPaymentMethods)) {
                    unset($subscriptionPaymentMethods[$code]);
                }
            }
        }
        return $subscriptionPaymentMethods;
    }
}
