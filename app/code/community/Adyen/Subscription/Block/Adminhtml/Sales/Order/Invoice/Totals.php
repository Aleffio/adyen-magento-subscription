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

class Adyen_Subscription_Block_Adminhtml_Sales_Order_Invoice_Totals extends Adyen_Fee_Block_Adminhtml_Sales_Order_Invoice_Totals {

    protected function _initTotals()
    {
        parent::_initTotals();

        $this->addTotal(
            new Varien_Object(
                array(
                    'code'      => 'subscription_fee',
                    'strong'    => false,
                    'value'     => $this->getSource()->getSubscriptionFeeAmount(),
                    'base_value'=> $this->getSource()->getBaseSubscriptionFeeAmount(),
                    'label'     => $this->helper('adyen_subscription')->__('Subscription Fee'),
                    'area'      => '',
                )
            ),
            'subtotal'
        );

        return $this;
    }
}