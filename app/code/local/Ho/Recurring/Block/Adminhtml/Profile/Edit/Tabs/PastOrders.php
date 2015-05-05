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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_PastOrders extends Mage_Adminhtml_Block_Widget_Grid
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
        $helper = Mage::helper('ho_recurring');

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
            'header'    => Mage::helper('ho_recurring')->__('Action'),
            'width'     => '140px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('ho_recurring')->__('View Order'),
                    'url'       => array(
                        'base'  => 'adminhtml/sales_order/view',
                    ),
                    'field'     => 'order_id',
                    'target'    => '_blank',
                ),
            ),
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Ho_Recurring_Model_Profile
     */
    public function getProfile()
    {
        return Mage::registry('ho_recurring');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('ho_recurring')->__('Past Orders');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('ho_recurring')->__('Past Orders');
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
