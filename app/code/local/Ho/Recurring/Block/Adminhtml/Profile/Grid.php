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

        $this->setDefaultSort('name');
        $this->setId('ho_recurring_profile_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _getCollectionClass()
    {
        return 'ho_recurring/profile_collection';
    }

    protected function _prepareCollection()
    {
        /** @var Ho_Recurring_Model_Resource_Profile_Collection $collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $collection->join(array('customer' => 'customer/entity'), 'customer_id = customer.entity_id', 'email');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ho_recurring');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('ID'),
            'align'     =>'right',
            'width'     => '80px',
            'index'     => 'entity_id',
        ));

        $this->addColumn('status', array(
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('ho_recurring/profile')->getStatuses(),
        ));

        $this->addColumn('customer_email', array(
            'header'    => $helper->__('Customer Email'),
            'index'     => 'email',
        ));

        $this->addColumn('customer_name', array(
            'header'    => $helper->__('Name'),
            'index'     => 'customer_name',
        ));

        $this->addColumn('payment_method', array(
            'header'    => $helper->__('Payment method'),
            'index'     => 'payment_method',
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Created at'),
            'index'     => 'created_at',
        ));

        $this->addColumn('ends_at', array(
            'header'    => $helper->__('Ends at'),
            'index'     => 'ends_at',
        ));

        $this->addColumn('next_shipment', array(
            'header'    => $helper->__('Next shipment'),
            // @todo Show date of next order
        ));

        $this->addColumn('action',
            array(
                'header'    => $helper->__('Actions'),
                'width'     => '200px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $helper->__('View'),
                        'url'     => array(
                            'base'  => '*/*/edit',
                            'params'=> array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
            ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
