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

class Adyen_Subscription_Block_Adminhtml_Subscription_View_Tabs_Scheduled_Info
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getSubscription()->getActiveQuote();
    }

    /**
     * @return string
     */
    public function getShippingMethodTitle()
    {
        $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod();
        $shippingCode = substr($shippingMethod, strpos($shippingMethod, '_') + 1);

        return $shippingTitle = Mage::getStoreConfig('carriers/' . $shippingCode . '/title');
    }

    /**
     * @return Adyen_Payment_Model_Billing_Agreement
     */
    public function getBillingAgreement()
    {
        return $this->getQuote()->getPayment()->getMethodInstance()->getBillingAgreement();
    }

    /**
     * @return string
     */
    public function getBillingAgreementViewUrl()
    {
        return $this->getUrl('adminhtml/sales_billing_agreement/view', array('agreement' => $this->getBillingAgreement()->getId()));
    }
}
