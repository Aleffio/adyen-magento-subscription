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
 
class Ho_Recurring_Model_System_Config_Source_Profile_Groups
{

    protected $_options;

    /**
     * Retrieve allowed for edit websites
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $collection = Mage::getResourceModel('customer/group_collection');
            $this->_options = ['' => [
                'value' => null,
                'label' => Mage::helper('ho_recurring')->__('All Customer Groups')
            ]];

            foreach ($collection as $item) {
                /** @var $item Mage_Customer_Model_Group */
                $this->_options[$item->getId()] = [
                    'value' => $item->getId(),
                    'label' => $item->getCustomerGroupCode()
                ];
            }
        }

        return $this->_options;
    }
}
