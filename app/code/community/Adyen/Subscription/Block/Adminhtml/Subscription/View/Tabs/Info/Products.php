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

class Adyen_Subscription_Block_Adminhtml_Subscription_View_Tabs_Info_Products
    extends Mage_Adminhtml_Block_Widget_Grid
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
        $collection = $this->_getSubscription()->getItemCollection();
        $collection->addRowTotalInclTax();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        /** @var Adyen_Subscription_Helper_Data $helper */
        $helper = Mage::helper('adyen_subscription');

        $currencyCode = (string) Mage::getStoreConfig(
            Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE,
            $this->_getSubscription()->getStoreId()
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

        // Min and max billing cycle currently not in use
//        $this->addColumn('min_billing_cycles', array(
//            'header'    => $helper->__('Min. B.C.'),
//            'type'      => 'number',
//            'index'     => 'min_billing_cycles',
//            'sortable'  => false,
//            'width'     => 1
//        ));
//
//        $this->addColumn('max_billing_cycles', array(
//            'header'    => $helper->__('Max. B.C.'),
//            'index'     => 'max_billing_cycles',
//            'type'      => 'number',
//            'title'     => 'sdfasdfasf',
//            'sortable'  => false,
//            'width'     => 1
//        ));

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
            'options'   => Mage::getModel('adyen_subscription/subscription_item')->getStatuses(),
            'sortable'  => false,
            'width'     => 80
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    protected function _getSubscription()
    {
        return Mage::registry('adyen_subscription');
    }
}
