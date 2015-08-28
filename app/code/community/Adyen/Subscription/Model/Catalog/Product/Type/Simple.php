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
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple
{
    /**
     * Check if product is configurable
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function canConfigure($product = null)
    {
        if (isset($product->getAttributes()['adyen_subscription_type'])) {
            if ($product->getData('adyen_subscription_type') != Adyen_Subscription_Model_Product_Subscription::TYPE_DISABLED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare selected options for simple subscription product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option = $buyRequest->getData('adyen_subscription');

        $options = array(
            'adyen_subscription'     => $option,
        );

        return $options;
    }
}
