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

// @todo Don't depend on Ho_SimpleBundle module (or check if it's installed)
class Adyen_Subscription_Model_Catalog_Product_Type_Bundle extends Ho_SimpleBundle_Model_Bundle_Product_Type
{
    /**
     * Prepare selected options for simple subscription product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = parent::processBuyRequest($product, $buyRequest);

        $option = $buyRequest->getData('adyen_subscription');

        $options['adyen_subscription'] = $option;

        return $options;
    }
}
