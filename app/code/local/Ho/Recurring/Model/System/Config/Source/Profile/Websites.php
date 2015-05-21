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
 
class Ho_Recurring_Model_System_Config_Source_Profile_Websites
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
