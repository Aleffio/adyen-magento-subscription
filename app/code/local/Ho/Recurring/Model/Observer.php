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

class Ho_Recurring_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @return string
     */
    public function createQuotes()
    {
        $profiles = Mage::getModel('ho_recurring/profile')->getActiveProfiles();

        $i = 0;
        foreach ($profiles as $profile) {
            /** @var Ho_Recurring_Model_Profile $profile */
            $profile->createQuote();
            $i++;
        }

        return Mage::helper('ho_recurring')->__('Quotes created for %s profiles', $i);
    }

    /**
     * @return string
     */
    public function createOrders()
    {
        $profiles = Mage::getModel('ho_recurring/profile')->getActiveProfiles();

        $i = 0;
        foreach ($profiles as $profile) {
            /** @var Ho_Recurring_Model_Profile $profile */
            if (!$profile->getQuoteId()) {
                $profile->createQuote();
            }

            $profile->createOrder();
            $i++;
        }

        return Mage::helper('ho_recurring')->__('Orders created for %s profiles', $i);
    }

    /**
     * Load recurring product profiles as custom option at product
     *
     * @todo Loading this custom option causes the loading of this option in the Custom Options tab in the backend,
     * @todo this must be avoided
     *
     * @event catalog_product_load_after
     * @param Varien_Event_Observer $observer
     */
    public function addRecurringProductProfilesOption(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();

        /** @var Mage_Catalog_Model_Product_Option $option */
        $option = Mage::getModel('catalog/product_option')
            ->setId(Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID)
            ->setProductId($product->getId())
            ->setType(Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO)
            ->setTitle(Mage::helper('ho_recurring')->__('Recurring Profile'))
            ->setProduct($product);

        $recurringProductProfiles = Mage::getModel('ho_recurring/product_profile')
            ->getCollection()
            ->addFieldToFilter('product_id', $product->getId());

        $i = 0;
        foreach ($recurringProductProfiles as $productProfile) {
            /** @var Ho_Recurring_Model_Product_Profile $productProfile */
            $value = Mage::getModel('catalog/product_option_value')->setData(
                    array(
                        'option_type_id'        => $productProfile->getId(),
                        'sort_order'            => $i,
                        'default_title'         => $productProfile->getLabel(),
                        'title'                 => $productProfile->getLabel(),
                        'default_price'         => $productProfile->getPrice(),
                        'default_price_type'    => 'fixed',
                        'price'                 => $productProfile->getPrice(),
                        'price_type'            => 'fixed',
                    )
                )->setOption($option);

            $option->addValue($value);
            $i++;
        }

        // Add the recurring profile option to the product
        $product->addOption($option);

        // Set the has_options attribute to true, or else the custom options won't be loaded on the frontend
        $product->setHasOptions(true);
    }

    /**
     * Add the selected recurring product profile to the quote item, when one is selected
     *
     * @event catalog_product_load_after
     * @param Varien_Event_Observer $observer
     */
    public function addRecurringProductProfileToQuote(Varien_Event_Observer $observer)
    {
        $productProfileOptionId = Ho_Recurring_Model_Product_Profile::CUSTOM_OPTION_ID;

        /** @var Mage_Checkout_CartController $action */
        $action = Mage::app()->getFrontController()->getAction();

        if ($action->getFullActionName() == 'checkout_cart_add') {
            $options = $action->getRequest()->getParam('options');

            if (array_key_exists($productProfileOptionId, $options)) {
                $recurringOption = $options[$productProfileOptionId];

                /** @var Mage_Catalog_Model_Product $product */
                $product = $observer->getProduct();

                $additionalOptions = array();
                if ($additionalOption = $product->getCustomOption('additional_options')) {
                    $additionalOptions = (array) unserialize($additionalOption->getValue());
                }

                // Add the product profile ID to the additional options array
                $additionalOptions[] = array(
                    'label' => $productProfileOptionId,
                    'value' => $recurringOption,
                );

                $product
                    ->addCustomOption('additional_options', serialize($additionalOptions));
            }
        }
    }
}
