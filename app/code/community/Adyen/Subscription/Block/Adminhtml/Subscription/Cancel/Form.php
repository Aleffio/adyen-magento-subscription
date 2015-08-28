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

class Adyen_Subscription_Block_Adminhtml_Subscription_Cancel_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/cancelPost'),
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $helper = Mage::helper('adyen_subscription');

        $fieldset = $form->addFieldset('general', array(
            'legend'    => $helper->__('Cancel'),
            'class'     => 'fieldset-wide'
        ));

        $fieldset->addField('id', 'hidden', array(
            'name'      => 'id',
        ))->setValue($this->getRequest()->getParam('id'));

        $fieldset->addField('reason', 'select', array(
            'name'      => 'reason',
            'label'     => $helper->__('Reason'),
            'values'    => $this->_getReasons(),
            'required'  => true,
        ));

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    protected function _getReasons()
    {
        $helper = Mage::helper('adyen_subscription');

        $options = array();

        $options[] = array(
            'value' => '',
            'label' => $helper->__('-- Select an option --'),
        );

        $reasons = Mage::helper('adyen_subscription/config')->getCancelReasons();

        foreach ($reasons as $reason) {
            $options[] = array(
                'value' => $reason['code'],
                'label' => $reason['label'],
            );
        }

        return $options;
    }
}
