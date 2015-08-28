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
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, Sander Mangel <sander@sandermangel.nl>
 */

class Adyen_Subscription_Block_Adminhtml_Subscription_View_Comments extends Mage_Adminhtml_Block_Template
{
    protected function _prepareLayout()
    {
        $this->setTemplate('adyen_subscription/subscription/view/comments.phtml');

        $onclick = "submitAndReloadArea($('subscription_comments_block').parentNode, '".$this->getSubmitUrl()."')";
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Submit Comment'),
                'class'   => 'save',
                'onclick' => $onclick
            ));

        $this->setChild('comment_submit_button', $button);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve subscription model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSubscription()
    {
        return Mage::registry('adyen_subscription');
    }

    public function getSubmitUrl()
    {
        return $this->getUrl("adyen_subscription/adminhtml_comments/save", array('subscription' => $this->getSubscription()->getId()));
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return Mage::helper('adminhtml/sales')->escapeHtmlWithLinks($data, $allowedTags);
    }

    public function getHistory()
    {
        $collection = Mage::getResourceModel('adyen_subscription/subscription_history_collection')
            ->addFieldToSelect(array('date', 'description', 'status'))
            ->addFieldToFilter('subscription_id', $this->getSubscription()->getId())
            ->setOrder('date','DESC');

        $adminUserTable = Mage::getSingleton('core/resource')->getTableName('admin/user');
        $collection->getSelect()->joinLeft(
            array('admin' => $adminUserTable), 'admin.user_id=main_table.user_id',
            array('administrator' => 'username')
        );

        $customerTable = Mage::getSingleton('core/resource')->getTableName('customer/entity');
        $collection->getSelect()->joinLeft(
            array('customer' => $customerTable), 'customer.entity_id=main_table.customer_id',
            array('customer' => 'email')
        );

        return $collection;
    }

    public function getUsername($history)
    {
        $html = "";

        $customer = $history->getData('customer');
        if (!empty($customer)) {
            $html .= Mage::helper('adyen_subscription')->__('customer') . ": {$customer}<br/>\r\n";
        }
        $admin = $history->getData('administrator');
        if (!empty($admin)) {
            $html .= Mage::helper('adyen_subscription')->__('administrator') . ": {$admin}<br/>\r\n";
        }

        if (!strlen($html)) {
            $html .= Mage::helper('adyen_subscription')->__('automated');
        }

        return $html;
    }
}