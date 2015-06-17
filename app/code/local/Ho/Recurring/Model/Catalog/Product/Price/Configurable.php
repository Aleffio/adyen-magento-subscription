<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_Recurring
 * @copyright   Copyright © 2015 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 * @author      Maikel Koek – H&O <info@h-o.nl>
 */

class Ho_Recurring_Model_Catalog_Product_Price_Configurable extends Mage_Catalog_Model_Product_Type_Configurable_Price
{
    /**
     * Get product final price
     * Extended to return profile price when product is recurring
     *
     * @param float|null $qty
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getFinalPrice($qty = null, $product)
    {
        if ($profileItem = $this->_helper()->getProfileItem($product)) {
            return $profileItem->getPriceInclTax();
        }

        if ($profile = $this->_helper()->getProductProfile($product)) {
            return $profile->getPrice();
        }

        return parent::getFinalPrice($qty, $product);
    }

    /**
     * @return Ho_Recurring_Helper_Quote
     */
    protected function _helper()
    {
        return Mage::helper('ho_recurring/quote');
    }
}
