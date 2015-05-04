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

class Ho_Recurring_Block_Adminhtml_Profile_Edit_Tabs_UpcomingOrders extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('upcoming_orders_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::registry('ho_recurring');

        $collection = Mage::getModel('sales/quote')
            ->getCollection()
            ->addFieldToFilter('entity_id', $profile->getQuoteIds());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ho_recurring');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('Quote #'),
            'index'     => 'entity_id',
        ));

        $this->addColumn('created_at', array(
            'header'    => $helper->__('Created At'),
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
            'currency' => 'quote_currency_code',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('ho_recurring')->__('Upcoming Orders');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('ho_recurring')->__('Upcoming Orders');
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
