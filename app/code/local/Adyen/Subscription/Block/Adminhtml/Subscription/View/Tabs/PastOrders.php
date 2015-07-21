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

class Adyen_Subscription_Block_Adminhtml_Profile_View_Tabs_PastOrders
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('past_orders_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $profile = $this->getProfile();

        $orderIds = $profile->getOrderIds();
        if ($orderIds) {
            $collection = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('entity_id', $orderIds);
        }
        else {
            $collection = array();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('adyen_subscription');

        $this->addColumn('increment_id', array(
            'header'    => $helper->__('Order #'),
            'index'     => 'increment_id',
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Purchased On'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => '100px',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('grid_action', array(
            'header'    => Mage::helper('adyen_subscription')->__('Action'),
            'width'     => '140px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('adyen_subscription')->__('View Order'),
                    'url'       => array(
                        'base'  => 'adminhtml/sales_order/view',
                    ),
                    'field'     => 'order_id',
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/sales_order/view', ['order_id' => $row->getId()]);
    }

    /**
     * @return Adyen_Subscription_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adyen_subscription')->__('Past Orders');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adyen_subscription')->__('Past Orders');
    }

    /**
     * Don't show tab if there are no orders
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getProfile()->getOrderIds();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
