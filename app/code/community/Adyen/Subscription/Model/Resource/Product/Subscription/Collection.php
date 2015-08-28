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

class Adyen_Subscription_Model_Resource_Product_Subscription_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('adyen_subscription/product_subscription');
    }


    /**
     * @param int|Mage_Catalog_Model_Product $productId
     * @return $this
     */
    public function addProductFilter($productId)
    {
        if ($productId instanceof Mage_Catalog_Model_Product) {
            $productId = $productId->getId();
        }

        $this->addFieldToFilter('product_id', $productId);
        return $this;
    }


    /**
     * @param Mage_Core_Model_Store $store
     * @return $this
     */
    public function addStoreFilter(Mage_Core_Model_Store $store)
    {
        $this->addFieldToFilter('website_id', ['in' => [0, (int) $store->getWebsiteId()]]);
        return $this;
    }
}

