<?php
/**
 *               _
 *              | |
 *     __ _   _ | | _  _   ___  _ __
 *    / _` | / || || || | / _ \| '  \
 *   | (_| ||  || || || ||  __/| || |
 *    \__,_| \__,_|\__, | \___||_||_|
 *                  __/ |
 *                 |___/
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Catalog_Product_Tab_Recurring_Price
    extends Varien_Data_Form_Element_Text
{
    public function getAfterElementHtml()
    {
        $html = $this->getData('after_element_html');

        $product = Mage::registry('product');
        $storeId = $product->getStoreId();

        $store = Mage::app()->getStore($storeId);
        $html.= '<strong>['.(string)$store->getBaseCurrencyCode().']</strong>';
        if (Mage::helper('tax')->priceIncludesTax($store)) {
            $inclTax = Mage::helper('tax')->__('Inc. Tax');
            $html.= " <strong>[{$inclTax} <span id=\"dynamic-tax-profile-{$this->getData('identifier')}\"></span>]</strong>";
        }

        return $html;
    }

    public function getEscapedValue($index=null)
    {
        $value = $this->getValue();

        if (!is_numeric($value)) {
            return null;
        }

        return number_format($value, 2, null, '');
    }
}
