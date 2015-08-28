<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */
 
class Adyen_Subscription_Model_System_Config_Source_Subscription_Groups
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
                'label' => Mage::helper('adyen_subscription')->__('All Customer Groups')
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
