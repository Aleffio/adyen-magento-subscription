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
 
class Adyen_Subscription_Block_Adminhtml_Sales_Order_Create_Header
    extends Mage_Adminhtml_Block_Sales_Order_Create_Header {

    /**
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Adyen_Subscription_Model_Subscription $subscription */
        $subscription = Mage::registry('current_subscription');

        if (! $subscription) {
            return parent::_toHtml();
        }


        if ($this->getRequest()->getParam('full_update')) {
            $out = Mage::helper('adyen_subscription')->__(
                'Edit Subscription #%s for %s in %s',
                $subscription->getIncrementId(),
                $subscription->getCustomer()->getName(),
                $this->getStore()->getName()
            );
        } else {
            $out = Mage::helper('adyen_subscription')->__(
                'Edit Scheduled Order for Adyen Subscription #%s for %s in %s',
                $subscription->getIncrementId(),
                $subscription->getCustomer()->getName(),
                $this->getStore()->getName()
            );
        }

        $out = $this->escapeHtml($out);
        $out = '<h3 class="icon-head head-sales-order">' . $out . '</h3>';
        return $out;
    }
}
