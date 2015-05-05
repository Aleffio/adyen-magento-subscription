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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_Products extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('products_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::registry('ho_recurring');

        $collection = $profile->getItems();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ho_recurring');

        $this->addColumn('sku', array(
            'header'    => $helper->__('SKU'),
            'index'     => 'sku',
        ));

        $this->addColumn('name', array(
            'header'    => $helper->__('Product Name'),
            'index'     => 'name',
            'width'     => '100px',
        ));

        $this->addColumn('price', array(
            'header'    => $helper->__('Price'),
            'index'     => 'price',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
        ));

        $this->addColumn('price_incl_tax', array(
            'header'    => $helper->__('Price Incl VAT'),
            'index'     => 'price_incl_tax',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
        ));

        $this->addColumn('qty', array(
            'header'    => $helper->__('Qty'),
            'index'     => 'qty',
        ));

        $this->addColumn('row_total', array(
            'header'    => $helper->__('Row Total'),
            'renderer'  => 'Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_Renderer_RowTotal',
        ));

        $this->addColumn('once', array(
            'header'    => $helper->__('Once'),
            'index'     => 'once',
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Added at'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('status', array(
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('ho_recurring/profile_item')->getStatuses(),
        ));

        $this->addColumn('action', array(
            'header'    => $helper->__('Action'),
            // @todo render
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('ho_recurring')->__('Products');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('ho_recurring')->__('Products');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
