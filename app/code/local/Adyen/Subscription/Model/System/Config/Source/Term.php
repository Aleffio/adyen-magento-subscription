<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                 |___/
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Model_System_Config_Source_Term
{
    protected $_options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $options = Mage::getModel('adyen_subscription/product_profile')->getTermTypes();

            array_unshift($options, array(
                'value' => '',
                'label' => Mage::helper('adyen_subscription')->__('-- Please Select --'),
            ));

            $this->_options = $options;
        }

        return $this->_options;
    }
}
