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

class Adyen_Subscription_Block_Customer_Subscription_Cancel extends Mage_Core_Block_Template
{
    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        $subscription = Mage::registry('adyen_subscription');

        return $subscription;
    }

    /**
     * @return array
     */
    public function getReasons()
    {
        $helper = Mage::helper('adyen_subscription');

        $options = array();

        $options[] = array(
            'value' => '',
            'label' => $helper->__('-- Select an option --'),
        );

        $reasons = Mage::helper('adyen_subscription/config')->getCancelReasons();

        foreach ($reasons as $reason) {
            $options[] = array(
                'value' => $reason['code'],
                'label' => $helper->__($reason['label']),
            );
        }

        return $options;
    }

    /**
     * Set data to block
     *
     * @return string
     */
    protected function _toHtml()
    {

        if($this->getSubscription()) {
            $this->setBackUrl(
                $this->getUrl('adyen_subscription/customer/view', array(
                    '_current' => true)));
            $this->setFormAction(
                $this->getUrl('adyen_subscription/customer/cancelPost', array(
                    '_current' => true)));
        }


        return parent::_toHtml();
    }

}