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

class Ho_Recurring_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple
{
    /**
     * Set qty of recurring product profile on buyRequest,
     * this will be saved at the cart product by parent::_prepareProduct
     *
     * @param  Varien_Object $buyRequest
     * @param  Mage_Catalog_Model_Product $product
     * @param  string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $recurringProfileId = $buyRequest->getData('ho_recurring_profile');
        if ($recurringProfileId) {
            $recurringProfile = Mage::getModel('ho_recurring/product_profile')->load($recurringProfileId);
            if ($recurringProfile->getId()) {
                $buyRequest->setQty($recurringProfile->getQty());
            }
        }

        return parent::_prepareProduct($buyRequest, $product, $processMode);
    }

    /**
     * Check if product is configurable
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function canConfigure($product = null)
    {
        if (isset($product->getAttributes()['ho_recurring_type'])) {
            if ($product->getData('ho_recurring_type') != Ho_Recurring_Model_Product_Profile::TYPE_DISABLED) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare selected options for simple profile product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $option = $buyRequest->getData('ho_recurring_profile');

        $options = array(
            'ho_recurring_profile'     => $option,
        );

        return $options;
    }
}
