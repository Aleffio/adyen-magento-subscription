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
        $collection = $this->_getProfile()->getItemCollection();
        $collection->addRowTotalInclTax();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Ho_Recurring_Helper_Data $helper */
        $helper = Mage::helper('ho_recurring');

        $currencyCode = (string) Mage::getStoreConfig(
            Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE,
            $this->_getProfile()->getStoreId()
        );

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
            'header'    => $helper->__('Price') .' '. $helper->__('Excl. Tax'),
            'index'     => 'price',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'currency_code' => $currencyCode,
            'sortable'  => false,
            'width'     => 60
        ));

        $this->addColumn('price_incl_tax', array(
            'header'    => $helper->__('Price') .' '. $helper->__('Incl. Tax'),
            'index'     => 'price_incl_tax',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'currency_code' => $currencyCode,
            'sortable'  => false,
            'width'     => 60
        ));

        $this->addColumn('qty', array(
            'header'    => $helper->__('Qty'),
            'type'      => 'number',
            'index'     => 'qty',
            'sortable'  => false,
            'width'     => 40
        ));

        $this->addColumn('row_total_incl_tax', array(
            'header'    => $helper->__('Row Total'),
            'index'     => 'row_total_incl_tax',
            'type'      => 'currency',
            'currency'  => 'base_currency_code',
            'currency_code' => $currencyCode,
            'sortable'  => false,
            'width'     => 60
        ));

        $this->addColumn('min_billing_cycles', array(
            'header'    => $helper->__('Min. B.C.'),
            'type'      => 'number',
            'index'     => 'min_billing_cycles',
            'sortable'  => false,
            'width'     => 1
        ));

        $this->addColumn('max_billing_cycles', array(
            'header'    => $helper->__('Max. B.C.'),
            'index'     => 'max_billing_cycles',
            'type'      => 'number',
            'title'     => 'sdfasdfasf',
            'sortable'  => false,
            'width'     => 1
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Added at'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'sortable'  => false,
            'width'     => 140
        ));

        $this->addColumn('status', array(
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getModel('ho_recurring/profile_item')->getStatuses(),
            'sortable'  => false,
            'width'     => 80
        ));

        $this->addColumn('action', array(
            'header'    => $helper->__('Action'),
            'sortable'  => false,
            'width'     => 80
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
