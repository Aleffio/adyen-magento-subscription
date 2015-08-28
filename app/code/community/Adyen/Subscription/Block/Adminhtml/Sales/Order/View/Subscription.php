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
 * @method $this setSubscription(Adyen_Subscription_Model_Subscription $subscription)
 * @method Adyen_Subscription_Model_Subscription getSubscription()
 * @see admin/subscription/sales/order/view/subscription.phtml
 */
class Adyen_Subscription_Block_Adminhtml_Sales_Order_View_Subscription
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    /**
     * @return mixed
     */
    public function getOrderInfoData()
    {
        return $this->getParentBlock()->getOrderInfoData();
    }

    protected function _toHtml()
    {
        $order = $this->getOrder();
        $subscription = Mage::getModel('adyen_subscription/subscription')->loadByOrder($order);

        if (! $subscription->getId()) {
            return $this->getChildHtml();
        }

        $this->setSubscription($subscription);
        return parent::_toHtml();
    }

    /**
     * @return Adyen_Subscription_Model_Subscription_Order
     */
    public function getSubscriptionOrderAdditionalInfo()
    {
        return $this->getSubscription()->getOrderAdditional($this->getOrder());
    }


    /**
     * @return Adyen_Subscription_Model_Subscription_Quote|null
     */
    public function getSubscriptionQuoteAdditionalInfo()
    {
        $quoteAdditional = Mage::getModel('adyen_subscription/subscription_quote')
            ->load($this->getOrder()->getQuoteId(), 'quote_id');

        return $quoteAdditional->getId() ? $quoteAdditional : null;
    }
}
