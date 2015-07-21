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

//@todo move to Product_Profile?
class Adyen_Subscription_Model_System_Config_Source_Profile_Type
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'label' => Mage::helper('adyen_subscription')->__('Profile Disabled'),
                    'value' => Adyen_Subscription_Model_Product_Profile::TYPE_DISABLED
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Standalone purchase possible'),
                    'value' => Adyen_Subscription_Model_Product_Profile::TYPE_ENABLED_ALLOW_STANDALONE
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Profile option selection required'),
                    'value' => Adyen_Subscription_Model_Product_Profile::TYPE_ENABLED_ONLY_PROFILE
                ]
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColums()
    {
        return array(
            $this->getAttribute()->getAttributeCode() => array(
                'type'      => 'tinyint',
                'unsigned'  => true,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null,
        ));
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceSingleton('eav/entity_attribute')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
