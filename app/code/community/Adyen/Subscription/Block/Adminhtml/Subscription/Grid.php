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

class Adyen_Subscription_Block_Adminhtml_Subscription_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('created_at');
        $this->setId('adyen_subscription_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        /** @var Adyen_Subscription_Model_Resource_Subscription_Collection $collection */
        $collection = Mage::getResourceModel('adyen_subscription/subscription_collection');
        $collection->addEmailToSelect();
        $collection->addNameToSelect();
        $collection->addBillingAgreementToSelect();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('adyen_subscription');

        $this->addColumn('entity_id', [
            'header'    => $helper->__('ID'),
            'align'     =>'right',
            'width'     => 1,
            'index'     => 'entity_id',
            'filter_index' => 'main_table.entity_id',
        ]);

        $this->addColumn('increment_id', [
            'header'    => $helper->__('Increment ID'),
            'index'     => 'increment_id',
            'width'     => 1,
            'filter_index' => 'main_table.increment_id',
        ]);

        $this->addColumn('error_message', [
            'header'    => $helper->__('Error Message'),
            'index'     => 'error_message',
        ]);

        $this->addColumn('customer_email', [
            'header'    => $helper->__('Customer Email'),
            'index'     => 'customer_email',
            'filter_index' => 'ce.email'
        ]);

        $this->addColumn('customer_name', [
            'header'    => $helper->__('Name'),
            'index'     => 'customer_name',
        ]);

        $this->addColumn('ba_method_code', [
            'type'      => 'options',
            'header'    => $helper->__('Payment method'),
            'index'     => 'ba_method_code',
            'options'   => Mage::helper('payment')->getAllBillingAgreementMethods(),
            'filter_index' => 'ba.method_code'
        ]);

        $this->addColumn('ba_reference_id', [
            'header'    => $helper->__('Billing Agreement'),
            'index'     => 'ba_reference_id',
            'filter_index' => 'ba.reference_id'
        ]);

        $this->addColumn('created_at', [
            'header'    => $helper->__('Created at'),
            'index'     => 'created_at',
            'type'      => 'datetime'
        ]);

//        $this->addColumn('ends_at', [
//            'header'    => $helper->__('Ends at'),
//            'index'     => 'ends_at',
//            'type'      => 'datetime'
//        ]);
//
//        $this->addColumn('next_order_at', [
//            'header'    => $helper->__('Next shipment'),
//            'index'     => 'next_order_at',
//            'type'      => 'datetime'
//        ]);

        $this->addColumn('status', [
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Adyen_Subscription_Model_Subscription::getStatuses(),
            'renderer'  => 'Adyen_Subscription_Block_Adminhtml_Subscription_Renderer_Status',
            'filter_index' => 'main_table.status'
        ]);

        $this->addColumn('action', [
            'header'    => $helper->__('Actions'),
            'width'     => '1',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [[
                'caption' => $helper->__('View'),
                'url'     => [
                    'base'  => 'adyen_subscription/adminhtml_subscription/view',
                    'params'=> ['store' => $this->getRequest()->getParam('store')]
                ],
                'field'   => 'id'
            ]],
            'filter'    => false,
            'sortable'  => false,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('subscription_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('adyen_subscription')->__('Delete'),
            'url'  => $this->getUrl('*/*/massDelete', array('' => '')),        // public function massDeleteAction() in Adyen_Subscription_Adminhtml_SubscriptionController
            'confirm' => Mage::helper('adyen_subscription')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adyen_subscription/adminhtml_subscription/view', array('id' => $row->getId()));
    }
}
