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

class Adyen_Subscription_Block_Adminhtml_Catalog_Product_Tab_Subscription_Price
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
            $html.= " <strong>[{$inclTax} <span id=\"dynamic-tax-{$this->getHtmlId()}\"></span>]</strong>";
        }

        if (! is_numeric($this->getIdentifier())) {
            return $html;
        }

        $data = array(
            'name' => str_replace('[price]', '[update_price]', $this->getData('name')),
            'disabled' => true,
        );

        $hidden =  new Varien_Data_Form_Element_Hidden($data);
        $hidden->setForm($this->getForm());

        $data['html_id'] = str_replace('[price]', '[update_price]', $this->getHtmlId());
        $data['label'] = Mage::helper('adyen_subscription')->__(
            'Update prices of all existing subscriptions',
            $this->getData('subscription_count')
        );
        $data['value'] = 1;

        $checkbox = new Varien_Data_Form_Element_Checkbox($data);
        $checkbox->setForm($this->getForm());
        $checkbox->getElementHtml();

        return $html . "<br />\n". $checkbox->getElementHtml() . $checkbox->getLabelHtml();
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
