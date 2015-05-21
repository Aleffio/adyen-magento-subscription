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
 
class Ho_Recurring_Model_System_Config_Source_Profile_Type
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const TYPE_DISABLED                 = 0;
    const TYPE_ENABLED_ALLOW_STANDALONE = 1;
    const TYPE_ENABLED_ONLY_PROFILE     = 2;


    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                [
                    'label' => Mage::helper('ho_recurring')->__('Profile Disabled'),
                    'value' => self::TYPE_DISABLED
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Standalone purchase possible'),
                    'value' => self::TYPE_ENABLED_ALLOW_STANDALONE
                ],[
                    'label' => Mage::helper('eav')->__('Profile Enabled + Profile option selection required'),
                    'value' => self::TYPE_ENABLED_ONLY_PROFILE
                ]
            ];
        }
        return $this->_options;
    }
}
