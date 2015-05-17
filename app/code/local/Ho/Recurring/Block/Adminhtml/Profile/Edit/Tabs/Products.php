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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_Products
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('products_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->_getProfile()->getItemCollection());

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ho_recurring');

        $this->addColumn('sku', array(
            'header'    => $helper->__('SKU'),
            'index'     => 'sku',
            'sortable'  => false
        ));

        $this->addColumn('name', array(
            'header'    => $helper->__('Product Name'),
            'index'     => 'name',
            'width'     => '100px',
            'sortable'  => false
        ));

        $this->addColumn('price', array(
            'header'    => $helper->__('Price'),
            'index'     => 'price',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'sortable'  => false
        ));

        $this->addColumn('price_incl_tax', array(
            'header'    => $helper->__('Price Incl VAT'),
            'index'     => 'price_incl_tax',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'sortable'  => false
        ));

        $this->addColumn('qty', array(
            'header'    => $helper->__('Qty'),
            'type'      => 'number',
            'index'     => 'qty',
            'sortable'  => false
        ));

        $this->addColumn('row_total', array(
            'header'    => $helper->__('Row Total'),
            'index'     => 'price_incl_tax',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'renderer'  => 'ho_recurring/adminhtml_profile_edit_tabs_renderer_rowTotal',
            'sortable'  => false
        ));

        $this->addColumn('once', array(
            'header'    => $helper->__('Once'),
            'index'     => 'once',
            'sortable'  => false
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Added at'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'sortable'  => false
        ));

        $this->addColumn('status', array(
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('ho_recurring/profile_item')->getStatuses(),
            'sortable'  => false
        ));

        $this->addColumn('action', array(
            'header'    => $helper->__('Action'),
            'sortable'  => false
            // @todo render
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Ho_Recurring_Model_Profile
     */
    protected function _getProfile()
    {
        return Mage::registry('ho_recurring');
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
