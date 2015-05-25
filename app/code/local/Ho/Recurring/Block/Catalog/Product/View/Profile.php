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

class Ho_Recurring_Block_Catalog_Product_View_Profile extends Mage_Core_Block_Template
{

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }


    /**
     * @return bool
     */
    public function canOrderStandalone()
    {
        return $this->getProfileType() == Ho_Recurring_Model_Product_Profile::TYPE_ENABLED_ALLOW_STANDALONE;
    }

    public function getJsonConfig()
    {
        $json = [];
        $json['none'] = $this->_getPriceStandaloneConfiguration();
        foreach ($this->getProfileCollection() as $profile) {
            /** @var Ho_Recurring_Model_Product_Profile $profile */
            $json[$profile->getId()] = $this->_getPriceProfileConfiguration($profile);
        }
        return json_encode($json);
    }

    protected function _getPriceStandaloneConfiguration()
    {
        $data = array();
        $data['price']      = 0;
        return $data;
    }

    /**
     * Get price configuration
     *
     * @param Ho_Recurring_Model_Product_Profile $profile
     * @return array
     */
    protected function _getPriceProfileConfiguration($profile)
    {
        $data = array();
        $data['price']      = Mage::helper('core')->currency($profile->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['oldPrice']   = Mage::helper('core')->currency($profile->getPrice() - $this->getProduct()->getFinalPrice(), false, false);
        $data['priceValue'] = $profile->getPrice(false);
//        $data['type']       = $option->getPriceType();
        $data['excludeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], false);
        $data['includeTax'] = $price = Mage::helper('tax')->getPrice($this->getProduct(), $data['price'], true);
        return $data;
    }

    /**
     * @return mixed
     */
    public function getProfileType()
    {
        return $this->getProduct()->getData('ho_recurring_type');
    }

    /**
     * @return Ho_Recurring_Model_Resource_Product_Profile_Collection
     */
    public function getProfileCollection()
    {
        return $this->getProduct()->getData('ho_recurring_data');
    }
}
