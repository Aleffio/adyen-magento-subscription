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

class Adyen_Subscription_Block_Adminhtml_Customer_Edit_Tab_Subscription
    extends Adyen_Subscription_Block_Adminhtml_Subscription_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Columns, that should be removed from grid
     *
     * @var array
     */
    protected $_columnsToRemove = array(
        'customer_id',
        'customer_email',
        'customer_name'
    );

    /**
     * Disable filters and paging
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_edit_tab_subscription');
        $this->setUseAjax(true);
    }

    /**
     * Do not show mass actions in tab view
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /** @var Adyen_Subscription_Model_Resource_Subscription_Collection $collection */
        $collection = Mage::getResourceModel('adyen_subscription/subscription_collection')
            ->addEmailToSelect()
            ->addNameToSelect()
            ->addBillingAgreementToSelect()
            ->addFieldToFilter('main_table.customer_id', Mage::registry('current_customer')->getId());
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Remove some columns and make other not sortable
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $result = parent::_prepareColumns();

        foreach ($this->_columns as $key => $value) {
            if (in_array($key, $this->_columnsToRemove)) {
                unset($this->_columns[$key]);
            }
        }
        return $result;
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Adyen Subscriptions');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Adyen Subscriptions');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/subscription/customerGrid', array('_current'=>true));
    }
    
    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'orders';
    }
}
