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

class Adyen_Subscription_Model_Resource_Subscription extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('adyen_subscription/subscription', 'entity_id');
    }

    public function loadByOrder(
        Adyen_Subscription_Model_Subscription $object,
        Mage_Sales_Model_Order $order
    ) {
        $orderSelect = Mage::getResourceModel('adyen_subscription/subscription_order_collection')
            ->addFieldToFilter('order_id', $order->getId())
            ->getSelect();

        $orderSelect->reset($orderSelect::COLUMNS);
        $orderSelect->columns('subscription_id');

        $subscriptionId = $this->_getConnection('read')->fetchOne($orderSelect);

        $this->load($object, $subscriptionId);

        return $this;
    }


    /**
     * @param Adyen_Subscription_Model_Subscription $object
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $this->setNewIncrementId($object);
        return parent::_beforeSave($object);
    }


    /**
     * Set new increment id to object
     *
     * @param Adyen_Subscription_Model_Subscription $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setNewIncrementId(Adyen_Subscription_Model_Subscription $object)
    {
        if ($object->getIncrementId() && $object->getIncrementId() != $object->getId()) {
            return $this;
        }

        $incrementId = Mage::getSingleton('eav/config')
            ->getEntityType('adyen_subscription')
            ->fetchNewIncrementId($object->getStoreId());

        if ($incrementId !== false) {
            $object->setIncrementId($incrementId);
        }

        return $this;
    }
}
