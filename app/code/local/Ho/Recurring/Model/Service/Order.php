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

class Ho_Recurring_Model_Service_Order extends Mage_Core_Model_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @return Ho_Recurring_Model_Profile
     */
    public function createProfile(Mage_Sales_Model_Order $order)
    {

        $billingAgreement = $this->_getBillingAgreement($order);

        /** @var Ho_Recurring_Model_Profile $profile */
        $profile = Mage::getModel('ho_recurring/profile')
            ->setStatus(Ho_Recurring_Model_Profile::STATUS_ACTIVE)
            ->setCustomerId($order->getCustomerId())
            ->setCustomerName($order->getCustomerName())
            ->setOrderId($order->getId())
            ->setBillingAgreementId($billingAgreement->getId())
            ->setStoreId($order->getStoreId())
            ->setEndsAt('2015-10-01 12:00:00') // @todo Set correct ending date
            ->setTerm(Ho_Recurring_Model_Profile::TERM_3_MONTHS) // @todo Set correct term
            ->setNextOrderAt('2015-06-01 12:00:00') // @todo Set correct date
            ->setPaymentMethod($billingAgreement->getMethodCode())
            ->setShippingMethod($order->getShippingMethod())
            ->save();

        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var Mage_Sales_Model_Order_Item $orderItem */
            /** @var Ho_Recurring_Model_Profile_Item $item */
            $item = Mage::getModel('ho_recurring/profile_item');
            $item->setProfileId($profile->getId());

            $item->setProductId($orderItem->getProductId())
                ->setSku($orderItem->getSku())
                ->setName($orderItem->getName())
                ->setPrice($orderItem->getPrice())
                ->setPriceInclTax($orderItem->getPriceInclTax())
                ->setQty($orderItem->getQtyOrdered())
                ->setOnce(0)
                ->setCreatedAt(now())
                ->setStatus($item::STATUS_ACTIVE);

            $item->save();
        }

        $profile->saveOrderAtProfile($order);

        return $profile;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _getBillingAgreement(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select();

        $select->from($resource->getTableName('sales/billing_agreement_order'));
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('agreement_id');
        $select->where('order_id = ?', $order->getId());

        $billingAgreementId = $connection->fetchOne($select);
        if (! $billingAgreementId) {
            Ho_Recurring_Exception::throwException('Could not find billing agreement for order '.$order->getIncrementId());
        }

        return Mage::getModel('sales/billing_agreement')->load($billingAgreementId);
    }
}
