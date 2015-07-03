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

//@todo move to Product_Profile?
class Ho_Recurring_Model_System_Config_Source_Profile_Type
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'label' => Mage::helper('ho_recurring')->__('Profile Disabled'),
                    'value' => Ho_Recurring_Model_Product_Profile::TYPE_DISABLED
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Standalone purchase possible'),
                    'value' => Ho_Recurring_Model_Product_Profile::TYPE_ENABLED_ALLOW_STANDALONE
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Profile option selection required'),
                    'value' => Ho_Recurring_Model_Product_Profile::TYPE_ENABLED_ONLY_PROFILE
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
