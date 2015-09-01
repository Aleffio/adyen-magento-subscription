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
 * Segment condition for sales rules
 */
class Adyen_Subscription_Model_SalesRule_Condition_QuoteCount extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * @var string
     */
    protected $_inputType = 'numeric';

    /**
     * Render element HTML
     *
     * @return string
     */
    public function asHtml()
    {
        $this->_valueElement = $this->getValueElement();
        return $this->getTypeElementHtml()
            . Mage::helper('adyen_subscription')->__(
                'Adyen Subscription: Number of created order (1 for the very first order) %s %s',
                $this->getOperatorElementHtml(), $this->_valueElement->getHtml()
            )
            . $this->getRemoveLinkHtml()
            . '<div class="rule-chooser" url="' . $this->getValueElementChooserUrl() . '"></div>';
    }


    /**
     * Validate if qoute customer is assigned to role segments
     *
     * @param Mage_Sales_Model_Quote_Address|Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $subscriptionId = $object->getQuote()->getSubscriptionId();
        $quoteCount = 1;

        if (! $subscriptionId) {
            $isSubscription = Mage::getSingleton('adyen_subscription/product_observer')
                ->isQuoteAdyenSubscription($object->getQuote());
            if (! $isSubscription) {
                return false;
            }
        } else {
            $pastOrders = Mage::getResourceModel('adyen_subscription/subscription_quote_collection')
                ->addFieldToFilter('order_id', array('notnull' => true))
                ->addFieldToFilter('subscription_id', $subscriptionId);

            $quoteCount += $pastOrders->getSize();
        }

        return $this->validateAttribute($quoteCount);
    }
}
