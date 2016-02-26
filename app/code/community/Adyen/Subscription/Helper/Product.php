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

class Adyen_Subscription_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return $this
     */
    public function loadProductSubscriptionData(Mage_Catalog_Model_Product $product)
    {
        if ($product->hasData('adyen_subscription_data')) {
            return $this;
        }
        /** @var Mage_Catalog_Model_Product $product */
        if ($product->getData('adyen_subscription_type') > 0) {
            $subscriptionCollection = Mage::getResourceModel('adyen_subscription/product_subscription_collection')
                ->addProductFilter($product);

            if (! $product->getStore()->isAdmin()) {
                $subscriptionCollection->addStoreFilter($product->getStore());
            }
            
            $subscriptionCollection->setOrder('sort_order','ASC');
            
            $product->setData('adyen_subscription_data', $subscriptionCollection);
        } else {
            $product->setData('adyen_subscription_data', null);
        }
        return $this;
    }
}
