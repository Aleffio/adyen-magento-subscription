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
 * @category  Ho
 * @package   Ho_Recurring
 * @author    Paul Hachmang – H&O <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */

class Ho_Recurring_Block_Adminhtml_Catalog_Product_Tab_Recurring_Price
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
