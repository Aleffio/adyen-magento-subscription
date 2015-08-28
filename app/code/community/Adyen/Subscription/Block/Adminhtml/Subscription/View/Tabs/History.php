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
 * Copyright (c) 2015 H&O (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O <info@h-o.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Subscription_View_Tabs_History
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('history_grid');
        $this->setDefaultSort('date', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $subscription = $this->getSubscription();

        $collection = Mage::getModel('adyen_subscription/subscription_history')
            ->getCollection()
            ->addFieldToFilter('subscription_id', $subscription->getId());

        $resource = Mage::getSingleton('core/resource');
        $adminUserTableName =  $resource->getTableName('admin/user');
        $customerTableName = $resource->getTableName('customer_entity');

        $collection->getSelect()->joinLeft(array('u' => $adminUserTableName), 'main_table.user_id=u.user_id', array('u.username'));
        $collection->getSelect()->joinLeft(array('c' => $customerTableName), 'main_table.customer_id=c.entity_id', array('c.email'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('adyen_subscription');

        $this->addColumn('entity_id', array(
            'header'    => $helper->__('History #'),
            'index'     => 'entity_id',
        ));

        $this->addColumn('date', array(
            'header'    => $helper->__('Date'),
            'index'     => 'date',
            'type'      => 'datetime',
            'width'     => '100px',
        ));

        $this->addColumn('username', array(
            'header' => $helper->__('Admin mail'),
            'index' => 'username',
            'type'  => 'integer'
        ));

        $this->addColumn('email', array(
            'header' => $helper->__('Customer mail'),
            'index' => 'email',
        ));

        $this->addColumn('status', [
            'header'    => $helper->__('Status'),
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Adyen_Subscription_Model_Subscription::getStatuses(),
            'renderer'  => 'Adyen_Subscription_Block_Adminhtml_Subscription_Renderer_Status',
            'filter_index' => 'main_table.status'
        ]);

        $this->addColumn('code', array(
            'header' => $helper->__('Code'),
            'index' => 'code',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return Adyen_Subscription_Model_Subscription
     */
    public function getSubscription()
    {
        return Mage::registry('adyen_subscription');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adyen_subscription')->__('History');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('adyen_subscription')->__('History');
    }

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
