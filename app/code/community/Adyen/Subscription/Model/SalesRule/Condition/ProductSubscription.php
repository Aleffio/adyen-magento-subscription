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
class Adyen_Subscription_Model_SalesRule_Condition_ProductSubscription extends Mage_Rule_Model_Condition_Abstract
{

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
                'Adyen Subscription: Product Subscription ID %s %s',
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
        $isSubscription = Mage::getSingleton('adyen_subscription/product_observer')
            ->isQuoteAdyenSubscription($object->getQuote());

        if (! $isSubscription) {
            return false;
        }

        foreach ($object->getQuote()->getAllVisibleItems() as $quoteItem) {
            $subscriptionId = $quoteItem->getData('_adyen_subscription');
            if (! $subscriptionId) {
                continue;
            }
            $validated = $this->validateAttribute($subscriptionId);
            if ($validated) {
                return true;
            }
        }

        return false;
    }
}
