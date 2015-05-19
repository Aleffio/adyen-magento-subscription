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

class Ho_Recurring_Block_Adminhtml_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('created_at');
        $this->setId('ho_recurring_profile_grid');
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
        /** @var Ho_Recurring_Model_Resource_Profile_Collection $collection */
        $collection = Mage::getResourceModel('ho_recurring/profile_collection');
        $collection->addEmailToSelect();
        $collection->addNameToSelect();
        $collection->addBillingAgreementToSelect();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ho_recurring');

        $this->addColumn('entity_id', [
            'header'    => $helper->__('ID'),
            'align'     =>'right',
            'width'     => 1,
            'index'     => 'entity_id',
        ]);

        $this->addColumn('status', [
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Ho_Recurring_Model_Profile::getStatuses(),
            'renderer'  => 'Ho_Recurring_Block_Adminhtml_Profile_Renderer_Status',
            'filter_index' => 'main_table.status'
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

        $this->addColumn('action', [
            'header'    => $helper->__('Actions'),
            'width'     => '1',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => [[
                'caption' => $helper->__('View'),
                'url'     => [
                    'base'  => '*/*/view',
                    'params'=> ['store' => $this->getRequest()->getParam('store')]
                ],
                'field'   => 'id'
            ]],
            'filter'    => false,
            'sortable'  => false,
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
