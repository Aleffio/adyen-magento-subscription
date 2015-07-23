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
 
class Adyen_Subscription_Model_System_Config_Source_Subscription_Websites
{

    protected $_options;

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!is_null($this->_options)) {
            return $this->_options;
        }

        $this->_options = array(
            0 => array(
                'value' => 0,
                'label' => sprintf('%s [%s]', Mage::helper('catalog')->__('All Websites'), Mage::app()->getBaseCurrencyCode())
            )
        );

        $isGlobal = Mage::app()->isSingleStoreMode();
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('product');

        if (!$isGlobal && $product->getStoreId()) {
            /** @var $website Mage_Core_Model_Website */
            $website = Mage::app()->getStore($product->getStoreId())->getWebsite();

            $this->_options[$website->getId()] = array(
                'value' => $website->getId(),
                'label' => sprintf('%s [%s]', $website->getName(), $website->getBaseCurrencyCode())
            );
        } elseif (!$isGlobal) {
            $websites = Mage::app()->getWebsites(false);
            $productWebsiteIds  = $product->getWebsiteIds();
            foreach ($websites as $website) {
                /** @var $website Mage_Core_Model_Website */
                if (!in_array($website->getId(), $productWebsiteIds)) {
                    continue;
                }
                $this->_options[$website->getId()] = array(
                    'value' => $website->getId(),
                    'label' => sprintf('%s [%s]', $website->getName(), $website->getBaseCurrencyCode()),
                );
            }
        }

        return $this->_options;
    }
}
