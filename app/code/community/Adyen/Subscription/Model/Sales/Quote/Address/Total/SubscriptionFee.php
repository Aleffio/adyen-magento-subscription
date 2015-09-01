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

class Adyen_Subscription_Model_Sales_Quote_Address_Total_SubscriptionFee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'subscription_fee';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $quote = $address->getQuote();

        $grandTotal = $address->getGrandTotal();

        $paymentMethod = $quote->getPayment()->getMethod();

        if($grandTotal == 0 && $address->getAllItems())
        {
            if($paymentMethod != "adyen_oneclick" && strpos($paymentMethod, 'adyen_oneclick') !== false)
            {
                $recurringDetails = $quote->getPayment()->getMethodInstance()->getRecurringDetails();
                if($recurringDetails['variant'] == "sepadirectdebit" || $recurringDetails['variant'] == "directEbanking") {
                    $this->setSubscriptionFee($address);
                }
            } else if(($paymentMethod == "adyen_oneclick") ||$paymentMethod == "adyen_ideal" || $paymentMethod == "adyen_hpp_sofort" ||
                    $paymentMethod == "adyen_sepa" || $paymentMethod == "adyen_hpp_sepa")
            {
                $this->setSubscriptionFee($address);
            }
        }

        return $this;
    }

    protected function setSubscriptionFee(Mage_Sales_Model_Quote_Address $address)
    {
        $fee = "0.01";
        $currentAmount = $address->getSubscriptionFeeAmount();

        $balance = $fee - $currentAmount;

        $address->setSubscriptionFeeAmount($address->getQuote()->getStore()->convertPrice($balance));
        $address->setBaseSubscriptionFeeAmount($balance);

        $address->setGrandTotal($address->getGrandTotal() + $address->getSubscriptionFeeAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseSubscriptionFeeAmount());
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $subscriptionFee = $address->getSubscriptionFeeAmount();

        if ($subscriptionFee != 0) {
            $fee = "0.01";
            $address->addTotal(array(
                    'code'=>$this->getCode(),
                    'title'=> Mage::helper('adyen')->__('Subscription Fee'),
                    'value'=> $subscriptionFee
            ));
        } else {
            $this->removeTotal($address, $this->getCode());
        }
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $code
     */
    protected function removeTotal(Mage_Sales_Model_Quote_Address $address, $code)
    {
        $reflectedClass = new ReflectionClass($address);
        $propertyTotals = $reflectedClass->getProperty('_totals');
        $propertyTotals->setAccessible(true);
        $totals = $propertyTotals->getValue($address);
        unset($totals[$code]);
        $propertyTotals->setValue($address, $totals);
    }
}